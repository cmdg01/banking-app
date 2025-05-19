<?php

namespace App\Services;

use App\Models\User;
use App\Models\Bank;
use DwollaSwagger\ApiException;
use DwollaSwagger\Configuration;
use Exception;
use Illuminate\Support\Facades\Log;

class DwollaService
{
    protected $apiClient;

    public function __construct()
    {
        $this->initApiClient();
    }

    /**
     * Initialize the Dwolla API client
     */
    protected function initApiClient()
    {
        $environment = config('dwolla.environment');
        $apiUrl = $environment === 'production' 
            ? 'https://api.dwolla.com' 
            : 'https://api-sandbox.dwolla.com';

        Configuration::$access_token = $this->getAccessToken();
        Configuration::$host = $apiUrl;

        $this->apiClient = new \DwollaSwagger\ApiClient(Configuration::getDefaultConfiguration());
    }

    /**
     * Get OAuth access token from Dwolla
     */
    protected function getAccessToken()
    {
        $environment = config('dwolla.environment');
        $apiUrl = $environment === 'production' 
            ? 'https://api.dwolla.com/token' 
            : 'https://api-sandbox.dwolla.com/token';

        $key = config('dwolla.key');
        $secret = config('dwolla.secret');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic " . base64_encode("$key:$secret"),
                "Content-Type: application/x-www-form-urlencoded"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            Log::error("Error getting Dwolla access token: " . $err);
            throw new Exception("Failed to connect to Dwolla API");
        }

        $result = json_decode($response);
        return $result->access_token;
    }

    /**
     * Create a customer in Dwolla
     *
     * @param User $user
     * @return array Containing customer_id and customer_url
     */
    public function createCustomer(User $user)
    {
        try {
            $customersApi = new \DwollaSwagger\CustomersApi($this->apiClient);
            
            // Log the user data for debugging (exclude sensitive info)
            Log::info('Creating Dwolla customer with user data:', [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ]);
            
            // Format date correctly
            $dateOfBirth = $user->date_of_birth;
            if ($dateOfBirth instanceof \DateTime || $dateOfBirth instanceof \Carbon\Carbon) {
                $dateOfBirth = $dateOfBirth->format('Y-m-d');
            }
            
            // Build the request
            $customerRequest = [
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
            
            // Include address2 only if it exists
            if (!empty($user->address_line_2)) {
                $customerRequest['address2'] = $user->address_line_2;
            }
            
            // Log the request (exclude sensitive info)
            $logRequest = array_diff_key($customerRequest, ['ssn' => '']);
            Log::info('Sending customer request to Dwolla:', $logRequest);
            
            // Create the customer
            $customer = $customersApi->create($customerRequest);
            
            // Extract customer ID from the URL
            $segments = explode('/', $customer->_links->self->href);
            $customerId = end($segments);
            
            $customerData = [
                'customer_id' => $customerId,
                'customer_url' => $customer->_links->self->href
            ];
            
            Log::info('Dwolla Customer Created:', $customerData);
            
            return $customerData;
        } catch (\DwollaSwagger\ApiException $e) {
            $responseBody = $e->getResponseBody();
            if (is_string($responseBody)) {
                try {
                    $responseBody = json_decode($responseBody, true);
                } catch (\Exception $jsonErr) {
                    // If can't decode as JSON, keep as string
                }
            }
            
            Log::error('Dwolla API Exception: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'response' => $responseBody
            ]);
            throw new \Exception('Failed to create Dwolla customer: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error creating Dwolla customer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getCustomerById($customerId)
    {
        try {
            $customersApi = new \DwollaSwagger\CustomersApi($this->apiClient);
            $customer = $customersApi->get($customerId);
            return $customer;
        } catch (ApiException $e) {
            Log::error('Failed to retrieve customer: ' . $e->getMessage());
            throw new Exception('Failed to retrieve customer: ' . $e->getMessage());
        }
    }
    
    /**
     * Initiate a transfer between funding sources
     *
     * @param array $transferData with the following keys:
     *   - source_url: The funding source URL for the source account
     *   - destination_url: The funding source URL for the destination account
     *   - amount: The amount to transfer (decimal)
     *   - note: Optional note for the transfer
     * @return array with the transfer ID and URL
     * @throws Exception
     */
    public function initiateTransfer(array $transferData)
    {
        try {
            $transfersApi = new \DwollaSwagger\TransfersApi($this->apiClient);
            
            // Format the amount to 2 decimal places
            $amount = number_format((float) $transferData['amount'], 2, '.', '');
            
            $transfer = [
                '_links' => [
                    'source' => [
                        'href' => $transferData['source_url']
                    ],
                    'destination' => [
                        'href' => $transferData['destination_url']
                    ]
                ],
                'amount' => [
                    'currency' => 'USD',
                    'value' => $amount
                ]
            ];
            
            // Add optional note if provided
            if (!empty($transferData['note'])) {
                $transfer['metadata'] = [
                    'note' => $transferData['note']
                ];
            }
            
            Log::info('Initiating Dwolla transfer:', [
                'source' => $transferData['source_url'],
                'destination' => $transferData['destination_url'],
                'amount' => $amount
            ]);
            
            $result = $transfersApi->create($transfer);
            
            // Get the transfer URL from the Location header
            $transferUrl = $result->_links->self->href;
            
            // Extract transfer ID from URL
            $segments = explode('/', $transferUrl);
            $transferId = end($segments);
            
            Log::info('Dwolla transfer created successfully', [
                'transfer_id' => $transferId,
                'transfer_url' => $transferUrl
            ]);
            
            return [
                'transfer_id' => $transferId,
                'transfer_url' => $transferUrl
            ];
            
        } catch (\DwollaSwagger\ApiException $e) {
            $responseBody = $e->getResponseBody();
            if (is_string($responseBody)) {
                try {
                    $responseBody = json_decode($responseBody, true);
                } catch (\Exception $jsonErr) {
                    // If can't decode as JSON, keep as string
                }
            }
            
            Log::error('Dwolla API Exception while initiating transfer: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'response' => $responseBody
            ]);
            throw new \Exception('Failed to initiate transfer: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error initiating Dwolla transfer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get transfer details by ID
     *
     * @param string $transferId
     * @return object
     * @throws Exception
     */
    public function getTransferById($transferId)
    {
        try {
            $transfersApi = new \DwollaSwagger\TransfersApi($this->apiClient);
            $transfer = $transfersApi->get($transferId);
            return $transfer;
        } catch (\DwollaSwagger\ApiException $e) {
            Log::error('Failed to retrieve transfer: ' . $e->getMessage());
            throw new Exception('Failed to retrieve transfer: ' . $e->getMessage());
        }
    }
}