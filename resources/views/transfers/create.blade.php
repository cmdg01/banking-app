<x-layouts.app :title="__('Transfer Funds')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Transfer Funds') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('transfers.store') }}" id="transferForm">
                        @csrf
                        
                        <!-- Source Account -->
                        <div class="mb-6">
                            <label for="source_bank_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('From Account') }}
                            </label>
                            <select name="source_bank_id" id="source_bank_id" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">{{ __('Select Source Account') }}</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">
                                        {{ $bank->institution_name }} - {{ $bank->account_name }} ({{ $bank->account_type }}) - *{{ $bank->account_mask }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Transfer Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Transfer To') }}
                            </label>
                            <div class="flex space-x-4">
                                <div class="flex items-center">
                                    <input id="own_account" name="destination_type" type="radio" value="own_account" checked
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <label for="own_account" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('My other account') }}
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="other_user" name="destination_type" type="radio" value="other_user"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <label for="other_user" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('Another user') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Own Account Section -->
                        <div id="own-account-section">
                            <div class="mb-6">
                                <label for="destination_bank_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('To Account') }}
                                </label>
                                <select name="destination_bank_id" id="destination_bank_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">{{ __('Select Destination Account') }}</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">
                                            {{ $bank->institution_name }} - {{ $bank->account_name }} ({{ $bank->account_type }}) - *{{ $bank->account_mask }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Other User Section (initially hidden) -->
                        <div id="other-user-section" class="hidden">
                            <div class="mb-6">
                                <label for="recipient_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Recipient Email') }}
                                </label>
                                <input type="email" name="recipient_email" id="recipient_email"
                                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="recipient@example.com">
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">The recipient must have an account in the system with a bank linked to Dwolla.</p>
                        </div>
                        
                        <!-- Amount -->
                        <div class="mb-6">
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Amount (USD)') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <x-input id="amount" class="block mt-1 w-full pl-7" type="number" name="amount" :value="old('amount')" step="0.01" min="0.01" max="10000.00" />
                            </div>
                        </div>
                        
                        <!-- Note -->
                        <div class="mb-6">
                            <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Note (Optional)') }}
                            </label>
                            <textarea name="note" id="note" rows="3"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                placeholder="Add a note about this transfer">{{ old('note') }}</textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <a href="{{ route('transfers.index') }}"
                                class="bg-white dark:bg-gray-600 py-2 px-4 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-button class="ml-4">
                                {{ __('Initiate Transfer') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ownAccountRadio = document.getElementById('own_account');
            const otherUserRadio = document.getElementById('other_user');
            const ownAccountSection = document.getElementById('own-account-section');
            const otherUserSection = document.getElementById('other-user-section');
            const destinationBankSelect = document.getElementById('destination_bank_id');
            const sourceBankSelect = document.getElementById('source_bank_id');

            // Function to toggle between own account and other user sections
            function toggleSections() {
                if (ownAccountRadio.checked) {
                    ownAccountSection.classList.remove('hidden');
                    otherUserSection.classList.add('hidden');
                    // Clear other user fields when switching to own account
                    document.getElementById('recipient_email').value = '';
                } else {
                    ownAccountSection.classList.add('hidden');
                    otherUserSection.classList.remove('hidden');
                    // Clear own account fields when switching to other user
                    if (destinationBankSelect) {
                        destinationBankSelect.value = '';
                    }
                }
            }
            
            // Prevent selecting the same account for both source and destination
            if (sourceBankSelect && destinationBankSelect) {
                sourceBankSelect.addEventListener('change', function() {
                    const selectedSource = this.value;
                    Array.from(destinationBankSelect.options).forEach(option => {
                        if (option.value === selectedSource) {
                            option.disabled = true;
                            if (destinationBankSelect.value === selectedSource) {
                                destinationBankSelect.value = '';
                            }
                        } else {
                            option.disabled = false;
                        }
                    });
                });

                // Initialize the destination select based on the initial source selection
                const event = new Event('change');
                sourceBankSelect.dispatchEvent(event);
            }
            
            ownAccountRadio.addEventListener('change', toggleSections);
            otherUserRadio.addEventListener('change', toggleSections);
        });
    </script>
</x-layouts.app>
