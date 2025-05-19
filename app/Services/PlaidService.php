<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Exception;

class PlaidService
{
    protected $client;
    protected $clientId;
    protected $secret;
    protected $environment;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('plaid.client_id');
        $this->secret = config('plaid.secret');
        $this->environment = config('plaid.environment', 'sandbox');

        // Set API URL based on environment
        $this->baseUrl = match ($this->environment) {
            'production' => 'https://production.plaid.com',
            'development' => 'https://development.plaid.com',
            default => 'https://sandbox.plaid.com',
        };

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            // IMPORTANT: Set to true in production for security
            'verify' => $this->environment !== 'production' ? false : true,
        ]);
    }

    /**
     * Create a Plaid Link token for a user
     */
    public function createLinkToken(User $user)
    {
        if (empty($this->clientId) || empty($this->secret)) {
            Log::error('Plaid client_id or secret is not configured.');
            throw new Exception('Plaid API credentials are not configured. Please check your .env file.');
        }

        try {
            // Prepare the payload with proper type casting
            $payload = [
                'client_id' => $this->clientId,
                'secret' => $this->secret,
                'user' => [
                    'client_user_id' => (string) $user->id,
                ],
                'client_name' => config('app.name', 'Banking App'), // Ensure app.name is set or provide a default
                'products' => config('plaid.products', ['auth']), // Ensure this is configured
                'country_codes' => config('plaid.country_codes', ['US']), // Ensure this is configured
                'language' => config('plaid.language', 'en'), // Ensure this is configured
                // 'webhook' => config('plaid.webhook_url'), // Optional: if you use webhooks
                // 'redirect_uri' => config('plaid.redirect_uri'), // Optional: if using OAuth redirect flow
            ];

            // Log the payload for debugging
            Log::debug('Plaid Link token request payload:', $payload);

            $response = $this->client->post('/link/token/create', [
                'json' => $payload,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            Log::debug('Plaid Link token success response', $result);

            if (!isset($result['link_token'])) {
                Log::error('link_token not found in Plaid response', ['response' => $result]);
                throw new Exception('Failed to retrieve link_token from Plaid.');
            }
            return $result['link_token'];

        } catch (GuzzleException $e) {
            // Capture the full response for better debugging
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'unknown';

            Log::error('Failed to create Plaid Link token: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'status_code' => $statusCode,
                'response' => $responseBody,
                'request' => $payload ?? null // Ensure payload is defined for logging
            ]);

            throw new Exception('Failed to initialize bank connection: ' . $e->getMessage() . '. Response: ' . substr($responseBody, 0, 200));
        } catch (Exception $e) { // Catch other potential exceptions
            Log::error('An unexpected error occurred while creating Plaid Link token: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'request' => $payload ?? null
            ]);
            throw $e; // Re-throw the exception
        }
    }

    // ... (rest of your PlaidService methods remain the same as you provided)
    /**
     * Exchange a public token for an access token
     */
    public function exchangePublicTokenForAccessToken(string $publicToken)
    {
        try {
            $response = $this->client->post('/item/public_token/exchange', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'public_token' => $publicToken,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return [
                'access_token' => $result['access_token'],
                'item_id' => $result['item_id'],
            ];
        } catch (GuzzleException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Failed to exchange Plaid public token: ' . $e->getMessage(), ['response' => $responseBody]);
            throw new Exception('Failed to complete bank connection: ' . $e->getMessage() . '. Response: ' . substr($responseBody, 0, 200));
        }
    }

    /**
     * Get account details using access token
     */
    public function getAccountDetails(string $accessToken, string $accountId = null)
    {
        try {
            $response = $this->client->post('/accounts/get', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'access_token' => $accessToken,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            // Get institution details
            $institutionId = $result['item']['institution_id']; // Corrected path to institution_id
            $institutionName = $this->getInstitutionName($institutionId);

            // If accountId is provided, return details for just that account
            if ($accountId) {
                foreach ($result['accounts'] as $account) {
                    if ($account['account_id'] === $accountId) {
                        return [
                            'institution_name' => $institutionName,
                            'account_name' => $account['name'],
                            'account_type' => $account['type'],
                            'account_mask' => $account['mask'],
                        ];
                    }
                }
                throw new Exception('Account not found');
            }

            // Otherwise return all accounts
            $accounts = [];
            foreach ($result['accounts'] as $account) {
                $accounts[] = [
                    'institution_name' => $institutionName,
                    'account_id' => $account['account_id'],
                    'account_name' => $account['name'],
                    'account_type' => $account['type'],
                    'account_mask' => $account['mask'],
                ];
            }

            return $accounts;
        } catch (GuzzleException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Failed to get Plaid account details: ' . $e->getMessage(), ['response' => $responseBody]);
            throw new Exception('Failed to retrieve bank account details: ' . $e->getMessage() . '. Response: ' . substr($responseBody, 0, 200));
        }
    }

    /**
     * Get institution name from an institution ID
     */
    protected function getInstitutionName(string $institutionId)
    {
        try {
            $response = $this->client->post('/institutions/get_by_id', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'institution_id' => $institutionId,
                    'country_codes' => config('plaid.country_codes', ['US']), // Ensure this is consistent
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['institution']['name'];
        } catch (GuzzleException $e) {
            // Default to generic name if we can't get the actual institution name
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::warning('Failed to get institution name: ' . $e->getMessage(), [
                'institution_id' => $institutionId,
                'response' => $responseBody
            ]);
            return 'Bank Account'; // Or consider re-throwing if critical
        }
    }

    /**
     * Create a processor token for Dwolla
     */
    public function createProcessorToken(string $accessToken, string $accountId, string $processor = 'dwolla')
    {
        try {
            $response = $this->client->post('/processor/token/create', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'access_token' => $accessToken,
                    'account_id' => $accountId,
                    'processor' => $processor,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['processor_token'];
        } catch (GuzzleException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Failed to create Plaid processor token: ' . $e->getMessage(), ['response' => $responseBody]);
            throw new Exception('Failed to prepare bank account for linking: ' . $e->getMessage() . '. Response: ' . substr($responseBody, 0, 200));
        }
    }

    /**
     * Get transactions for a bank account
     * 
     * @param string $accessToken The Plaid access token
     * @param string $startDate The start date in YYYY-MM-DD format
     * @param string $endDate The end date in YYYY-MM-DD format
     * @param string|null $accountId Optional account ID to filter by
     * @return array
     */
    public function getTransactions(string $accessToken, string $startDate, string $endDate, ?string $accountId = null)
    {
        try {
            $payload = [
                'client_id' => $this->clientId,
                'secret' => $this->secret,
                'access_token' => $accessToken,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            
            // Add account_id filter if provided
            if ($accountId) {
                $payload['options'] = [
                    'account_ids' => [$accountId]
                ];
            }

            $response = $this->client->post('/transactions/get', [
                'json' => $payload,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['transactions'];
        } catch (GuzzleException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Failed to get Plaid transactions: ' . $e->getMessage(), ['response' => $responseBody]);
            throw new Exception('Failed to retrieve transactions: ' . $e->getMessage() . '. Response: ' . substr($responseBody, 0, 200));
        }
    }
}