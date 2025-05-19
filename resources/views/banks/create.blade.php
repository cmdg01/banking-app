<x-layouts.app :title="__('Link Bank Account')">
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('banks.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('Back to Accounts') }}
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Link Your Bank Account') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-6 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('Secure Bank Connection') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Connect your bank account using our secure partner Plaid. Your login credentials are never stored on our servers.') }}
                        </p>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-500 p-4 mb-6 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h.01a1 1 0 100-2H10V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    {{ __('We use bank-level security and 256-bit encryption to keep your information safe.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <button id="link-button" 
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors duration-200">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Link Your Bank Account') }}
                        </button>
                        
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('By linking your account, you agree to our') }}
                            <a href="#" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                {{ __('Terms of Service') }}
                            </a>
                            {{ __('and') }}
                            <a href="#" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                {{ __('Privacy Policy') }}
                            </a>.
                        </p>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 text-center">
                    <div class="flex items-center justify-center space-x-4">
                        <img src="{{ asset('images/plaid-logo.png') }}" alt="Plaid" class="h-6 opacity-70">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Secured by Plaid') }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Supported Banks') }}
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach(['chase', 'bankofamerica', 'wellsfargo', 'citibank', 'usbank', 'capitalone', 'tdbank', 'pnc'] as $bank)
                            <div class="flex items-center justify-center p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <img src="{{ asset('images/banks/' . $bank . '.png') }}" 
                                     alt="{{ ucfirst($bank) }}" 
                                     class="h-8 opacity-80">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plaid Link Script -->
    <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const linkToken = "{{ $linkToken }}";
            const linkButton = document.getElementById('link-button');
            
            // Initialize Plaid Link
            const handler = Plaid.create({
                token: linkToken,
                onSuccess: function(public_token, metadata) {
                    // Send the public token and metadata to the server
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("banks.store") }}';
                    
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'public_token';
                    tokenInput.value = public_token;
                    form.appendChild(tokenInput);
                    
                    const metadataInput = document.createElement('input');
                    metadataInput.type = 'hidden';
                    metadataInput.name = 'metadata';
                    metadataInput.value = JSON.stringify(metadata);
                    form.appendChild(metadataInput);
                    
                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);
                    
                    document.body.appendChild(form);
                    form.submit();
                },
                onLoad: function() {
                    // Handle the Loaded event
                },
                onExit: function(err, metadata) {
                    // Handle the case when the user exits the flow
                    if (err != null) {
                        console.error('Plaid error:', err);
                    }
                },
                onEvent: function(eventName, metadata) {
                    // Log events for debugging
                    console.log('Plaid event:', eventName, metadata);
                },
            });

            // Add click handler for the link button
            linkButton.addEventListener('click', function(e) {
                e.preventDefault();
                handler.open();
            });
        });
    </script>
</x-layouts.app>
