<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DwollaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DwollaController extends Controller
{
    protected $dwollaService;

    public function __construct(DwollaService $dwollaService)
    {
        $this->dwollaService = $dwollaService;
    }

    public function createCustomer(Request $request)
    {
        try {
            // Validate the request
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

            // For testing, create a temporary user object
            $user = new User();
            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->email = $validated['email'];
            $user->address_line_1 = $validated['address_line_1'];
            $user->address_line_2 = $validated['address_line_2'] ?? null;
            $user->city = $validated['city'];
            $user->state = $validated['state'];
            $user->postal_code = $validated['postal_code'];
            $user->date_of_birth = $validated['date_of_birth'];
            $user->ssn = $validated['ssn'];

            // Create customer in Dwolla
            $customerData = $this->dwollaService->createCustomer($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => [
                    'customer_id' => $customerData['customer_id'],
                    'customer_url' => $customerData['customer_url']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Dwolla customer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createCustomerForExistingUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Create customer in Dwolla
            $customerData = $this->dwollaService->createCustomer($user);
            
            // Update user with Dwolla information
            $user->dwolla_customer_id = $customerData['customer_id'];
            $user->dwolla_customer_url = $customerData['customer_url'];
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => [
                    'customer_id' => $customerData['customer_id'],
                    'customer_url' => $customerData['customer_url']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Dwolla customer for user ID ' . $userId . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}