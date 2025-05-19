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
     */
    public function createFundingSourceWithProcessorToken(string $customerUrl, string $processorToken, string $bankName)
    {
        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
            // Prepare request data
            $data = [
                'plaidToken' => $processorToken,
                'name' => $bankName,
            ];
            
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
            
            Log::info('Dwolla funding source created successfully', [
                'funding_source_url' => $fundingSourceUrl
            ]);
            
            return $fundingSourceUrl;
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
            
            Log::error('Dwolla API error creating funding source: ' . $errorMessage, [
                'response_body' => $responseBody
            ]);
            
            throw new Exception('Failed to link bank account: ' . $errorMessage);
        }
    }

    /**
    * Initiate a transfer between funding sources
    * * @param string $sourceUrl The funding source URL to transfer from
    * @param string $destinationUrl The funding source URL to transfer to
    * @param float $amount The amount to transfer
    * @param string $currency The currency code (default: USD)
    * @param array $metadata Optional metadata for the transfer
    * @return array Transfer details including id and status
    * @throws Exception
    */
    public function createTransfer($sourceUrl, $destinationUrl, $amount, $currency = 'USD', $metadata = [])
    {
        try {
            // Get access token
            $accessToken = $this->getAccessToken();
            
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
            
            // Add metadata if provided
            if (!empty($metadata)) {
                $transferData['metadata'] = $metadata;
            }
            
            // Log the transfer request
            Log::info('Initiating Dwolla transfer', [
                'source' => $sourceUrl,
                'destination' => $destinationUrl,
                'amount' => $amount,
                'currency' => $currency
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
                'source' => $sourceUrl,
                'destination' => $destinationUrl,
                'amount' => $amount
            ]);
            
            throw new Exception('Failed to create transfer: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error creating Dwolla transfer: ' . $e->getMessage());
            throw $e;
        }
    }
}