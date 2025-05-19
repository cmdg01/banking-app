<x-layouts.app.sidebar>
    <div class="flex flex-col flex-1 overflow-x-hidden">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center">
                    <a href="{{ route('banks.index') }}" class="inline-flex items-center mr-4 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back
                    </a>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Link New Bank Account') }}</h1>
                </div>

                @if (session('error'))
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="mt-6 bg-white dark:bg-zinc-700 overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <p class="text-gray-700 dark:text-gray-300 mb-6">
                            {{ __('To link your bank account securely, we use Plaid as our trusted partner. Your banking credentials are never stored on our servers.') }}
                        </p>

                        <div class="flex flex-col space-y-4">
                            <div class="bg-gray-50 dark:bg-zinc-600 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('How It Works') }}</h3>
                                <ol class="list-decimal list-inside text-gray-600 dark:text-gray-300 space-y-2">
                                    <li>{{ __('Click the "Link Bank Account" button below') }}</li>
                                    <li>{{ __('Select your bank from the list') }}</li>
                                    <li>{{ __('Log in with your bank credentials through Plaid\'s secure interface') }}</li>
                                    <li>{{ __('Select the account you want to link') }}</li>
                                    <li>{{ __('Confirm your selection') }}</li>
                                </ol>
                            </div>

                            <div class="mt-4 text-center">
                                <button id="link-button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    {{ __('Link Bank Account') }}
                                </button>
                            </div>
                        </div>
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
                    form.action = "{{ route('banks.link.process') }}";

                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = "{{ csrf_token() }}";
                    form.appendChild(csrfInput);

                    // Add public token
                    const publicTokenInput = document.createElement('input');
                    publicTokenInput.type = 'hidden';
                    publicTokenInput.name = 'public_token';
                    publicTokenInput.value = public_token;
                    form.appendChild(publicTokenInput);

                    // Add institution info
                    const institutionIdInput = document.createElement('input');
                    institutionIdInput.type = 'hidden';
                    institutionIdInput.name = 'institution_id';
                    institutionIdInput.value = metadata.institution.institution_id;
                    form.appendChild(institutionIdInput);

                    const institutionNameInput = document.createElement('input');
                    institutionNameInput.type = 'hidden';
                    institutionNameInput.name = 'institution_name';
                    institutionNameInput.value = metadata.institution.name;
                    form.appendChild(institutionNameInput);

                    // Add account info (first account is selected by default)
                    if (metadata.accounts && metadata.accounts.length > 0) {
                        const accountIdInput = document.createElement('input');
                        accountIdInput.type = 'hidden';
                        accountIdInput.name = 'account_id';
                        accountIdInput.value = metadata.accounts[0].id;
                        form.appendChild(accountIdInput);
                    }

                    // Submit the form
                    document.body.appendChild(form);
                    form.submit();
                },
                onExit: function(err, metadata) {
                    // The user exited the Link flow
                    if (err !== null) {
                        // The user encountered an error
                        console.error('Plaid Link Error:', err);
                    }
                    // Metadata contains information about the selected institution
                    console.log('Plaid Link Exit Metadata:', metadata);
                },
                onEvent: function(eventName, metadata) {
                    // Log Plaid Link events for debugging
                    console.log('Plaid Link Event:', eventName, metadata);
                }
            });

            // Add click handler to launch Plaid Link
            linkButton.addEventListener('click', function() {
                handler.open();
            });
        });
    </script>
</x-layouts.app.sidebar>
