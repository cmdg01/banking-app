<x-layouts.app :title="__('Transactions')">
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Your Transactions</h1>
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

        <div class="bg-white dark:bg-zinc-800 shadow-md rounded-lg overflow-hidden">
            @if($transactions->isEmpty())
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    <p>No transactions found. Connect a bank to start tracking your transactions.</p>
                    <a href="{{ route('banks.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Connect a Bank
                    </a>
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
