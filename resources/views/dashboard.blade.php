<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Total Balance</h2>
                <p class="text-gray-800 dark:text-gray-200 text-2xl font-bold">${{ number_format($totalBalance, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Connected Banks</h2>
                <p class="text-gray-800 dark:text-gray-200 text-2xl font-bold">{{ $banks->count() }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Quick Actions</h2>
                <div class="flex gap-2">
                    <a href="{{ route('banks.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded">
                        Connect Bank
                    </a>
                    <a href="{{ route('transactions.index') }}" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium py-2 px-3 rounded">
                        View Transactions
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Recent Transactions</h2>
                <a href="{{ route('transactions.index') }}" class="text-blue-500 hover:text-blue-700">View All</a>
            </div>
            
            @if($recentTransactions->isEmpty())
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    <p>No transactions found. Connect a bank to start tracking your transactions.</p>
                    <a href="{{ route('banks.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Connect a Bank
                    </a>
                </div>
            @else
                <x-transaction-table :transactions="$recentTransactions" />
            @endif
        </div>
    </div>
</x-layouts.app>
