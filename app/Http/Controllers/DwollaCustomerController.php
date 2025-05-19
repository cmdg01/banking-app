<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DwollaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DwollaCustomerController extends Controller
{
    public function showForm()
    {
        return view('dwolla-customer-form');
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

            // Create a temporary user object with the validated data
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

            // Create the Dwolla customer
            $dwollaService = new DwollaService();
            $customerData = $dwollaService->createCustomer($user);
            
            return redirect()->back()->with('success', 'Customer created successfully! ID: ' . $customerData['customer_id']);
        } catch (\Exception $e) {
            Log::error('Failed to create Dwolla customer: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
}
