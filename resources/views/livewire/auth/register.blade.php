<?php

use App\Models\User;
use App\Services\DwollaService;
use App\Services\DirectDwollaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
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

        $validated['password'] = Hash::make($validated['password']);
        
        // Create the user
        $user = User::create($validated);
        
        // Create Dwolla customer - but don't stop registration if it fails
        try {
            $dwollaService = new \App\Services\DirectDwollaService();
            Log::info('Attempting to create Dwolla customer for newly registered user', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            // Create customer in Dwolla
            $customerData = $dwollaService->createCustomer($user);
            
            // Update user with Dwolla information
            $user->dwolla_customer_id = $customerData['customer_id'];
            $user->dwolla_customer_url = $customerData['customer_url'];
            $user->save();
            
            Log::info('Successfully created Dwolla customer for user', [
                'user_id' => $user->id,
                'dwolla_customer_id' => $customerData['customer_id'] 
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Dwolla customer during registration: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            // Continue with registration despite Dwolla API failure
        }

        event(new Registered($user));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; 
?>

<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Create your banking account')" 
        :description="__('Enter your details below to create your banking account')" 
    />
    
    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />
    
    <form wire:submit="register" class="flex flex-col gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <flux:input
                wire:model="name"
                :label="__('Name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />
            
            <!-- Email Address -->
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
            <!-- First Name -->
            <flux:input
                wire:model="first_name"
                :label="__('First name')"
                type="text"
                required
                autocomplete="given-name"
                :placeholder="__('First name')"
            />
            
            <!-- Last Name -->
            <flux:input
                wire:model="last_name"
                :label="__('Last name')"
                type="text"
                required
                autocomplete="family-name"
                :placeholder="__('Last name')"
            />
        </div>
        
        <!-- Address Line 1 -->
        <flux:input
            wire:model="address_line_1"
            :label="__('Address line 1')"
            type="text"
            required
            autocomplete="address-line1"
            :placeholder="__('Street address')"
        />
        
        <!-- Address Line 2 -->
        <flux:input
            wire:model="address_line_2"
            :label="__('Address line 2 (optional)')"
            type="text"
            autocomplete="address-line2"
            :placeholder="__('Apartment, suite, etc.')"
        />
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- City -->
            <flux:input
                wire:model="city"
                :label="__('City')"
                type="text"
                required
                autocomplete="address-level2"
                :placeholder="__('City')"
            />
            
            <!-- State -->
            <flux:input
                wire:model="state"
                :label="__('State')"
                type="text"
                required
                maxlength="2"
                autocomplete="address-level1"
                :placeholder="__('State (e.g. CA)')"
            />
            
            <!-- Postal Code -->
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
            <!-- Date of Birth -->
            <flux:input
                wire:model="date_of_birth"
                :label="__('Date of birth')"
                type="date"
                required
                autocomplete="bday"
                :helper="__('You must be at least 18 years old')"
            />
            
            <!-- SSN -->
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
            <!-- Password -->
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />
            
            <!-- Confirm Password -->
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