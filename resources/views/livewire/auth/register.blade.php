<?php

use App\Models\User;
use App\Services\DirectDwollaService; // Assuming DirectDwollaService is the one being used
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Added for database transactions
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Additional fields for Dwolla
    public string $first_name = '';
    public string $last_name = '';
    public string $address_line_1 = '';
    public string $address_line_2 = '';
    public string $city = '';
    public string $state = '';
    public string $postal_code = '';
    public string $date_of_birth = '';
    public string $ssn = '';

    // Property to hold registration error messages
    public string $registrationError = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'postal_code' => ['required', 'string', 'max:10'],
            'date_of_birth' => ['required', 'date', 'before:-18 years'],
            'ssn' => ['required', 'string', 'size:9'],
        ]);

        // Clear any previous registration errors
        $this->registrationError = '';

        try {
            $validated['password'] = Hash::make($validated['password']);

            // Start database transaction
            DB::beginTransaction();

            // Create the user
            $user = User::create($validated);

            // Create Dwolla customer
            try {
                $dwollaService = new \App\Services\DirectDwollaService();
                Log::info('Attempting to create Dwolla customer for newly registered user', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

                // Create customer in Dwolla
                $customerData = $dwollaService->createCustomer($user); // Assuming createCustomer now takes the validated data or user object

                // Update user with Dwolla information
                $user->dwolla_customer_id = $customerData['customer_id'];
                $user->dwolla_customer_url = $customerData['customer_url'];
                $user->save();

                Log::info('Successfully created Dwolla customer for user', [
                    'user_id' => $user->id,
                    'dwolla_customer_id' => $customerData['customer_id']
                ]);

                // Commit the transaction if everything is successful
                DB::commit();

            } catch (\Exception $e) {
                // Rollback the transaction if Dwolla customer creation or user update fails
                DB::rollBack();

                Log::error('Failed to create Dwolla customer or update user during registration: ' . $e->getMessage(), [
                    'user_id' => $user->id ?? null, // User ID might exist if creation was successful but Dwolla part failed
                    'trace' => $e->getTraceAsString()
                ]);

                // Set a user-friendly error message, including the specific error if desired
                $this->registrationError = 'Failed to finalize your account setup with our payment processor. Please try again or contact support. Error details: ' . $e->getMessage();
                return; // Stop further execution
            }

            event(new Registered($user));

            Auth::login($user);

            $this->redirectIntended(route('dashboard', absolute: false), navigate: true);

        } catch (\Exception $e) {
            // Rollback the transaction if any other part of the registration fails (e.g., user creation itself)
            DB::rollBack(); // Ensure rollback is called if transaction was started

            Log::error('Registration failed: ' . $e->getMessage(), [
                'email' => $validated['email'] ?? 'N/A', // Log email if available
                'trace' => $e->getTraceAsString()
            ]);

            $this->registrationError = 'Registration failed due to an unexpected error. Please try again. If the problem persists, contact support.';
            // No return here, Livewire will re-render and show the error
        }
    }
};
?>

<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Create your banking account')" 
        :description="__('Enter your details below to create your banking account')" 
    />

    @if ($registrationError)
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            {{ $registrationError }}
        </div>
    @endif
    
    <x-auth-session-status class="text-center" :status="session('status')" />
    
    <form wire:submit="register" class="flex flex-col gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input
                wire:model="name"
                :label="__('Name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />
            
            <flux:input
                wire:model="email"
                :label="__('Email address')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input
                wire:model="first_name"
                :label="__('First name')"
                type="text"
                required
                autocomplete="given-name"
                :placeholder="__('First name')"
            />
            
            <flux:input
                wire:model="last_name"
                :label="__('Last name')"
                type="text"
                required
                autocomplete="family-name"
                :placeholder="__('Last name')"
            />
        </div>
        
        <flux:input
            wire:model="address_line_1"
            :label="__('Address line 1')"
            type="text"
            required
            autocomplete="address-line1"
            :placeholder="__('Street address')"
        />
        
        <flux:input
            wire:model="address_line_2"
            :label="__('Address line 2 (optional)')"
            type="text"
            autocomplete="address-line2"
            :placeholder="__('Apartment, suite, etc.')"
        />
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <flux:input
                wire:model="city"
                :label="__('City')"
                type="text"
                required
                autocomplete="address-level2"
                :placeholder="__('City')"
            />
            
            <flux:input
                wire:model="state"
                :label="__('State')"
                type="text"
                required
                maxlength="2"
                autocomplete="address-level1"
                :placeholder="__('State (e.g. CA)')"
            />
            
            <flux:input
                wire:model="postal_code"
                :label="__('Postal code')"
                type="text"
                required
                autocomplete="postal-code"
                :placeholder="__('Postal code')"
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input
                wire:model="date_of_birth"
                :label="__('Date of birth')"
                type="date"
                required
                autocomplete="bday"
                :helper="__('You must be at least 18 years old')"
            />
            
            <flux:input
                wire:model="ssn"
                :label="__('Social Security Number')"
                type="password" 
                required
                maxlength="9"
                :helper="__('9 digits, no dashes')"
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />
            
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />
        </div>
        
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            <p>By creating an account, you agree to our terms of service and privacy policy.</p>
        </div>
        
        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create banking account') }}
            </flux:button>
        </div>
    </form>
    
    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>