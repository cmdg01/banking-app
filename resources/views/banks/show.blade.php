<x-layouts.app :title="__('Bank Details')">
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $bank->institution_name }}</h1>
            <div class="space-x-2">
                <a href="{{ route('banks.index') }}" class="inline-block bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium py-2 px-4 rounded">
                    Back to Banks
                </a>
                <form action="{{ route('transactions.sync', $bank) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Sync Transactions
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Account Type</h2>
                <p class="text-gray-800 dark:text-gray-200 text-xl">{{ ucfirst($bank->account_type) }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Account Name</h2>
                <p class="text-gray-800 dark:text-gray-200 text-xl">{{ $bank->account_name }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Last 4 Digits</h2>
                <p class="text-gray-800 dark:text-gray-200 text-xl">{{ $bank->account_mask }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Connected On</h2>
                <p class="text-gray-800 dark:text-gray-200 text-xl">{{ $bank->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-800 shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Recent Transactions</h2>
            </div>
            
            @if($transactions->isEmpty())
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    <p>No transactions found for this account.</p>
                    <form action="{{ route('transactions.sync', $bank) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Sync Transactions
                        </button>
                    </form>
                </div>
            @else
                <x-transaction-table :transactions="$transactions" />
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
