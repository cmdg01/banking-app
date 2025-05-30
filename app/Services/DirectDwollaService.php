<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Exception;

class DirectDwollaService
{
    protected $environment;
    protected $key;
    protected $secret;
    protected $baseUrl;
    
    public function __construct()
    {
        $this->environment = config('dwolla.environment', 'sandbox');
        $this->key = config('dwolla.key');
        $this->secret = config('dwolla.secret');
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.dwolla.com' 
            : 'https://api-sandbox.dwolla.com';
        
        if (empty($this->key) || empty($this->secret)) {
            throw new Exception('Dwolla API credentials are missing. Check your .env file.');
        }
    }
    
    /**
     * Get an access token from Dwolla
     */
    public function getAccessToken()
    {
        try {
            $client = new Client();
            
            $response = $client->post($this->baseUrl . '/token', [
                'auth' => [$this->key, $this->secret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ],
                'verify' => false // For development only
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $data['access_token'];
        } catch (\Exception $e) {
            Log::error('Failed to get Dwolla access token: ' . $e->getMessage());
            throw new Exception('Failed to connect to Dwolla API: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a customer in Dwolla
     */
    public function createCustomer(User $user)
    {
        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
            // Format date correctly
            $dateOfBirth = $user->date_of_birth;
            if ($dateOfBirth instanceof \DateTime || $dateOfBirth instanceof \Carbon\Carbon) {
                $dateOfBirth = $dateOfBirth->format('Y-m-d');
            }
            
            // Prepare customer data
            $customerData = [
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'type' => 'personal',
                'address1' => $user->address_line_1,
                'city' => $user->city,
                'state' => $user->state,
                'postalCode' => $user->postal_code,
                'dateOfBirth' => $dateOfBirth,
                'ssn' => $user->ssn
            ];
            
            // Add address2 if not empty
            if (!empty($user->address_line_2)) {
                $customerData['address2'] = $user->address_line_2;
            }
            
            // Log the request (excluding sensitive information)
            $logData = $customerData;
            unset($logData['ssn']);
            Log::info('Creating Dwolla customer:', $logData);
            
            // Make the API request
            $client = new Client();
            $response = $client->post($this->baseUrl . '/customers', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/vnd.dwolla.v1.hal+json',
                    'Accept' => 'application/vnd.dwolla.v1.hal+json'
                ],
                'json' => $customerData,
                'verify' => false // For development only
            ]);
            
            // Get customer URL from Location header
            $customerUrl = $response->getHeader('Location')[0] ?? null;
            
            if (empty($customerUrl)) {
                throw new Exception('No customer URL in Dwolla response');
            }
            
            // Extract customer ID from URL
            $segments = explode('/', $customerUrl);
            $customerId = end($segments);
            
            Log::info('Dwolla customer created successfully', [
                'customer_id' => $customerId,
                'customer_url' => $customerUrl
            ]);
            
            return [
                'customer_id' => $customerId,
                'customer_url' => $customerUrl
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            $responseBody = '';
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = (string) $response->getBody();
                
                try {
                    $errorData = json_decode($responseBody, true);
                    if (isset($errorData['_embedded']['errors'][0]['message'])) {
                        $errorMessage = $errorData['_embedded']['errors'][0]['message'];
                    }
                } catch (\Exception $jsonEx) {
                    // Just use the original error message
                }
            }
            
            Log::error('Dwolla API error: ' . $errorMessage, [
                'response_body' => $responseBody
            ]);
            
            throw new Exception('Dwolla API error: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error creating Dwolla customer: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get a customer's details
     */
    public function getCustomer($customerId)
    {
        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
            // Make the API request
            $client = new Client();
            $response = $client->get($this->baseUrl . '/customers/' . $customerId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/vnd.dwolla.v1.hal+json'
                ],
                'verify' => false // For development only
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Failed to get Dwolla customer: ' . $e->getMessage());
            throw new Exception('Failed to get Dwolla customer: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a funding source using a Plaid processor token
     * 
     * @param string $customerUrl The customer's URL from Dwolla
     * @param string $processorToken The processor token from Plaid
     * @param string $name Name for the funding source
     * @param string $type Type of account (checking or savings)
     * @return array Created funding source data
     */
    public function createFundingSource(string $customerUrl, string $processorToken, string $name, string $type = 'checking')
    {
        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
            // Prepare request data
            $data = [
                'plaidToken' => $processorToken,
                'name' => $name,
            ];
            
            // Add account type if provided
            if (in_array(strtolower($type), ['checking', 'savings'])) {
                $data['channels'] = [strtoupper($type)];
            }
            
            // Log the request (excluding sensitive data)
            Log::info('Creating Dwolla funding source', [
                'customer_url' => $customerUrl,
                'name' => $name,
                'type' => $type
            ]);
            
            // Make the API request
            $client = new Client();
            $response = $client->post($customerUrl . '/funding-sources', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/vnd.dwolla.v1.hal+json',
                    'Accept' => 'application/vnd.dwolla.v1.hal+json'
                ],
                'json' => $data,
                'verify' => false // For development only
            ]);
            
            // Get funding source URL from Location header
            $fundingSourceUrl = $response->getHeader('Location')[0] ?? null;
            
            if (empty($fundingSourceUrl)) {
                throw new Exception('No funding source URL in Dwolla response');
            }
            
            // Extract funding source ID from URL
            $segments = explode('/', $fundingSourceUrl);
            $fundingSourceId = end($segments);
            
            Log::info('Dwolla funding source created successfully', [
                'funding_source_id' => $fundingSourceId,
                'funding_source_url' => $fundingSourceUrl
            ]);
            
            return [
                'id' => $fundingSourceId,
                'url' => $fundingSourceUrl,
                'status' => 'pending' // Initial status
            ];
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            $responseBody = '';
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = (string) $response->getBody();
                
                try {
                    $errorData = json_decode($responseBody, true);
                    if (isset($errorData['_embedded']['errors'][0]['message'])) {
                        $errorMessage = $errorData['_embedded']['errors'][0]['message'];
                    }
                } catch (\Exception $jsonEx) {
                    // Just use the original error message
                }
            }
            
            Log::error('Dwolla funding source creation failed: ' . $errorMessage, [
                'response_body' => $responseBody,
                'customer_url' => $customerUrl
            ]);
            
            throw new Exception('Failed to create funding source: ' . $errorMessage);
            
        } catch (\Exception $e) {
            Log::error('Error creating Dwolla funding source: ' . $e->getMessage());
            throw new Exception('Failed to create funding source: ' . $e->getMessage());
        }
    }

    /**
     * Alias for createFundingSource for backward compatibility
     */
    public function createFundingSourceWithProcessorToken(string $customerUrl, string $processorToken, string $bankName)
    {
        return $this->createFundingSource($customerUrl, $processorToken, $bankName);
    }

    /**
     * Create a funding source for a customer using a processor token
     * 
     * @param string $customerUrl The customer's URL from Dwolla
     * @param string $processorToken The processor token from Plaid
     * @param string $name Name for the funding source
     * @param string $type Type of account (checking or savings)
     * @return array Created funding source data
     */
    public function createFundingSourceForCustomer(string $customerUrl, string $processorToken, string $name, string $type = 'checking')
    {
        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
            // Prepare request data
            $data = [
                'plaidToken' => $processorToken,
                'name' => $name,
            ];
            
            // Add account type if provided
            if (in_array(strtolower($type), ['checking', 'savings'])) {
                $data['channels'] = [strtoupper($type)];
            }
            
            // Log the request (excluding sensitive data)
            Log::info('Creating Dwolla funding source', [
                'customer_url' => $customerUrl,
                'name' => $name,
                'type' => $type
            ]);
            
            // Make the API request
            $client = new Client();
            $response = $client->post($customerUrl . '/funding-sources', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/vnd.dwolla.v1.hal+json',
                    'Accept' => 'application/vnd.dwolla.v1.hal+json'
                ],
                'json' => $data,
                'verify' => false // For development only
            ]);
            
            // Get funding source URL from Location header
            $fundingSourceUrl = $response->getHeader('Location')[0] ?? null;
            
            if (empty($fundingSourceUrl)) {
                throw new Exception('No funding source URL in Dwolla response');
            }
            
            // Extract funding source ID from URL
            $segments = explode('/', $fundingSourceUrl);
            $fundingSourceId = end($segments);
            
            Log::info('Dwolla funding source created successfully', [
                'funding_source_id' => $fundingSourceId,
                'funding_source_url' => $fundingSourceUrl
            ]);
            
            return [
                'id' => $fundingSourceId,
                'url' => $fundingSourceUrl,
                'status' => 'pending' // Initial status
            ];
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            $responseBody = '';
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = (string) $response->getBody();
                
                try {
                    $errorData = json_decode($responseBody, true);
                    if (isset($errorData['_embedded']['errors'][0]['message'])) {
                        $errorMessage = $errorData['_embedded']['errors'][0]['message'];
                    }
                } catch (\Exception $jsonEx) {
                    // Just use the original error message
                }
            }
            
            Log::error('Dwolla funding source creation failed: ' . $errorMessage, [
                'response_body' => $responseBody,
                'customer_url' => $customerUrl
            ]);
            
            throw new Exception('Failed to create funding source: ' . $errorMessage);
            
        } catch (\Exception $e) {
            Log::error('Error creating Dwolla funding source: ' . $e->getMessage());
            throw new Exception('Failed to create funding source: ' . $e->getMessage());
        }
    }

    /**
    * Initiate a transfer between funding sources
    * @param string $sourceUrl The funding source URL or ID to transfer from
    * @param string $destinationUrl The funding source URL or ID to transfer to
    * @param float $amount The amount to transfer
    * @param string $note Optional note for the transfer
    * @param string $currency The currency code (default: USD)
    * @return array Transfer details including id and status
    * @throws Exception
    */
    public function createTransfer($sourceUrl, $destinationUrl, $amount, $note = null, $currency = 'USD')
    {
        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
            // Check if we have full URLs or just IDs
            if (strpos($sourceUrl, 'http') !== 0) {
                $sourceUrl = $this->baseUrl . '/funding-sources/' . $sourceUrl;
            }
            
            if (strpos($destinationUrl, 'http') !== 0) {
                $destinationUrl = $this->baseUrl . '/funding-sources/' . $destinationUrl;
            }
            
            // Prepare transfer data
            $transferData = [
                '_links' => [
                    'source' => [
                        'href' => $sourceUrl
                    ],
                    'destination' => [
                        'href' => $destinationUrl
                    ]
                ],
                'amount' => [
                    'currency' => $currency,
                    'value' => number_format($amount, 2, '.', '')
                ]
            ];
            
            // Note: Removed metadata as it's not supported for this transfer type
            // If you need to include notes, consider using the transfer's description field instead
            
            // Log the transfer request
            Log::info('Initiating Dwolla transfer', [
                'source_url' => $sourceUrl,
                'destination_url' => $destinationUrl,
                'amount' => $amount,
                'currency' => $currency,
                'note' => $note
            ]);
            
            // Make the API request
            $client = new Client();
            $response = $client->post($this->baseUrl . '/transfers', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/vnd.dwolla.v1.hal+json',
                    'Accept' => 'application/vnd.dwolla.v1.hal+json'
                ],
                'json' => $transferData,
                'verify' => false // For development only
            ]);
            
            // Get transfer URL from Location header
            $transferUrl = $response->getHeader('Location')[0] ?? null;
            
            if (empty($transferUrl)) {
                throw new Exception('No transfer URL in Dwolla response');
            }
            
            // Extract transfer ID from URL
            $segments = explode('/', $transferUrl);
            $transferId = end($segments);
            
            Log::info('Dwolla transfer created successfully', [
                'transfer_id' => $transferId,
                'transfer_url' => $transferUrl
            ]);
            
            return [
                'id' => $transferId,
                'url' => $transferUrl,
                'status' => 'pending' // Initial status
            ];
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            $responseBody = '';
            
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = (string) $response->getBody();
                
                try {
                    $errorData = json_decode($responseBody, true);
                    if (isset($errorData['_embedded']['errors'][0]['message'])) {
                        $errorMessage = $errorData['_embedded']['errors'][0]['message'];
                    }
                } catch (\Exception $jsonEx) {
                    // Just use the original error message
                }
            }
            
            Log::error('Dwolla API error creating transfer: ' . $errorMessage, [
                'response_body' => $responseBody,
                'source_url' => $sourceUrl,
                'destination_url' => $destinationUrl,
                'amount' => $amount
            ]);
            
            throw new Exception('Failed to create transfer: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error creating Dwolla transfer: ' . $e->getMessage());
            throw $e;
        }
    }
}