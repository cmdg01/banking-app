<x-layouts.app :title="__('New Transfer')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('New Transfer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if (session('error'))
                    <div class="mb-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('transfers.store') }}" class="space-y-6">
                    @csrf

                    <!-- Source Account -->
                    <div>
                        <label for="source_bank_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            From Account
                        </label>
                        <select name="source_bank_id" id="source_bank_id" required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select Source Account</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">
                                    {{ $bank->institution_name }} - {{ $bank->account_name }} ({{ $bank->account_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Destination Account -->
                    <div>
                        <label for="destination_bank_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            To Account
                        </label>
                        <select name="destination_bank_id" id="destination_bank_id" required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select Destination Account</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">
                                    {{ $bank->institution_name }} - {{ $bank->account_name }} ({{ $bank->account_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Amount (USD)
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                        </div>
                    </div>

                    <!-- Note -->
                    <div>
                        <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Note (Optional)
                        </label>
                        <textarea name="note" id="note" rows="3"
                            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                            Initiate Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Simple client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const sourceBank = document.getElementById('source_bank_id').value;
            const destBank = document.getElementById('destination_bank_id').value;
            const amount = document.getElementById('amount').value;

            if (sourceBank === destBank) {
                e.preventDefault();
                alert('Source and destination accounts cannot be the same.');
                return false;
            }

            if (!sourceBank || !destBank || !amount) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            return true;
        });
    </script>
</x-layouts.app>