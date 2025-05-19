<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class DwollaDirectController extends Controller
{
    public function showForm()
    {
        return view('dwolla-direct-form');
    }
    
    public function createCustomer(Request $request)
    {
        try {
            // 1. Validate the form data
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'address_line_1' => 'required|string|max:255',
                'address_line_2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|size:2',
                'postal_code' => 'required|string|max:10',
                'date_of_birth' => 'required|date|before:-18 years',
                'ssn' => 'required|string|size:9',
            ]);
            
            // 2. Get environment info and API credentials
            $environment = config('dwolla.environment', 'sandbox');
            $key = config('dwolla.key');
            $secret = config('dwolla.secret');
            
            if (empty($key) || empty($secret)) {
                throw new \Exception('Dwolla API credentials are missing. Check your .env file.');
            }
            
            $baseUrl = $environment === 'production' 
                ? 'https://api.dwolla.com' 
                : 'https://api-sandbox.dwolla.com';
                
            // 3. First get an access token
            $client = new Client();
            
            $tokenResponse = $client->post($baseUrl . '/token', [
                'auth' => [$key, $secret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ],
                'verify' => false // For development only
            ]);
            
            $tokenData = json_decode($tokenResponse->getBody(), true);
            $accessToken = $tokenData['access_token'];
            
            // 4. Create the customer
            $customerData = [
                'firstName' => $validated['first_name'],
                'lastName' => $validated['last_name'],
                'email' => $validated['email'],
                'type' => 'personal',
                'address1' => $validated['address_line_1'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postalCode' => $validated['postal_code'],
                'dateOfBirth' => $validated['date_of_birth'],
                'ssn' => $validated['ssn']
            ];
            
            // Only add address2 if provided
            if (!empty($validated['address_line_2'])) {
                $customerData['address2'] = $validated['address_line_2'];
            }
            
            // Log what we're sending (excluding sensitive data)
            $logData = $customerData;
            unset($logData['ssn']); // Don't log SSN
            Log::info('Creating Dwolla customer with data:', $logData);
            
            $customerResponse = $client->post($baseUrl . '/customers', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/vnd.dwolla.v1.hal+json',
                    'Accept' => 'application/vnd.dwolla.v1.hal+json'
                ],
                'json' => $customerData,
                'verify' => false // For development only
            ]);
            
            // 5. Process the response
            $customerUrl = $customerResponse->getHeader('Location')[0] ?? null;
            
            if (empty($customerUrl)) {
                throw new \Exception('No customer URL in Dwolla response');
            }
            
            // Extract the customer ID from the URL
            $segments = explode('/', $customerUrl);
            $customerId = end($segments);
            
            Log::info('Dwolla customer created successfully', [
                'customer_id' => $customerId,
                'customer_url' => $customerUrl
            ]);
            
            return redirect()->back()->with('success', "Customer created successfully! ID: $customerId");
            
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
            
            return redirect()->back()
                ->with('error', 'API Error: ' . $errorMessage)
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error creating Dwolla customer: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }
}