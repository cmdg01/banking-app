<x-layouts.app :title="__('AI Financial Insights')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('AI Financial Insights') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">AI Financial Insights</h2>
                        <div class="flex space-x-3">
                            <div class="flex items-center px-3 py-1 rounded-full {{ $apiKeyConfigured ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }}">
                                <i class="fas fa-key mr-2 {{ $apiKeyConfigured ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                                <span class="{{ $apiKeyConfigured ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }} text-sm">API: {{ $apiKeyConfigured ? 'Connected' : 'Not Configured' }}</span>
                            </div>
                            <div class="flex items-center px-3 py-1 rounded-full {{ $hasData ? 'bg-green-100 dark:bg-green-900' : 'bg-yellow-100 dark:bg-yellow-900' }}">
                                <i class="fas fa-database mr-2 {{ $hasData ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}"></i>
                                <span class="{{ $hasData ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200' }} text-sm">Data: {{ $hasData ? 'Available' : 'Not Available' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- System Status Card -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl shadow-md overflow-hidden mb-8">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                    <i class="fas fa-cogs text-white text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">System Status</h2>
                                    <p class="text-gray-500 dark:text-gray-400">Current configuration and data availability</p>
                                </div>
                            </div>
                            
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">API Key Status</p>
                                            <div class="mt-1 flex items-center">
                                                @if($apiKeyConfigured)
                                                    <span class="h-3 w-3 rounded-full bg-green-500 mr-2 animate-pulse"></span>
                                                    <span class="text-base font-medium text-gray-900 dark:text-white">Configured</span>
                                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">({{ $apiKeyLength }} chars)</span>
                                                @else
                                                    <span class="h-3 w-3 rounded-full bg-red-500 mr-2"></span>
                                                    <span class="text-base font-medium text-gray-900 dark:text-white">Not Configured</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-{{ $apiKeyConfigured ? 'green' : 'red' }}-500 text-2xl">
                                            <i class="fas fa-{{ $apiKeyConfigured ? 'check-circle' : 'times-circle' }}"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Data Availability</p>
                                            <div class="mt-1 flex items-center">
                                                @if($hasData)
                                                    <span class="h-3 w-3 rounded-full bg-green-500 mr-2 animate-pulse"></span>
                                                    <span class="text-base font-medium text-gray-900 dark:text-white">Data Available</span>
                                                @else
                                                    <span class="h-3 w-3 rounded-full bg-yellow-500 mr-2"></span>
                                                    <span class="text-base font-medium text-gray-900 dark:text-white">No Data</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-{{ $hasData ? 'green' : 'yellow' }}-500 text-2xl">
                                            <i class="fas fa-{{ $hasData ? 'database' : 'exclamation-triangle' }}"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if(!$apiKeyConfigured)
                                <div class="mt-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-700 p-4 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-circle text-red-500 dark:text-red-400 text-lg"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">API Key Not Configured</h3>
                                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                                <p>Please add your Gemini API key to the .env file:</p>
                                                <pre class="mt-2 bg-gray-800 text-gray-200 p-2 rounded text-xs overflow-x-auto">GEMINI_API_KEY=your_api_key_here</pre>
                                                <p class="mt-2">You can get an API key from <a href="https://makersuite.google.com/app/apikey" class="underline text-red-700 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" target="_blank">Google AI Studio</a>.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!$hasData)
                                <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 dark:border-yellow-700 p-4 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-500 dark:text-yellow-400 text-lg"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No Financial Data Available</h3>
                                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                                <p>Please connect a bank account and import transactions to get insights.</p>
                                                <a href="{{ route('banks.link') }}" class="mt-2 inline-block px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                                                    <i class="fas fa-link mr-1"></i> Connect Bank Account
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($hasData && $apiKeyConfigured)
                        <!-- Financial Data Cards -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                            <!-- Summary Card -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-md overflow-hidden insights-card">
                                <div class="bg-blue-500 px-4 py-2">
                                    <h3 class="text-white font-medium">Financial Summary</h3>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-3">
                                        @foreach(($financialData['summary'] ?? []) as $key => $value)
                                            <div class="flex justify-between border-b border-gray-100 dark:border-gray-600 pb-2">
                                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Accounts Card -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-md overflow-hidden insights-card">
                                <div class="bg-indigo-500 px-4 py-2">
                                    <h3 class="text-white font-medium">Accounts ({{ count($financialData['accounts'] ?? []) }})</h3>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-4">
                                        @foreach(($financialData['accounts'] ?? []) as $account)
                                            <div class="border-b border-gray-100 dark:border-gray-600 pb-3 last:border-0 last:pb-0">
                                                <div class="flex justify-between items-center">
                                                    <div class="flex items-center">
                                                        <div class="bg-indigo-100 dark:bg-indigo-900 rounded-full p-2 mr-3">
                                                            <i class="fas fa-{{ $account['account_type'] == 'checking' ? 'money-check-alt' : ($account['account_type'] == 'savings' ? 'piggy-bank' : 'landmark') }} text-indigo-500 dark:text-indigo-400"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $account['account_name'] ?? 'Unnamed Account' }}</p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($account['account_type'] ?? 'Unknown Type') }}</p>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm font-medium px-2 py-1 rounded-full {{ ($account['balance_current'] ?? 0) >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                        ${{ number_format($account['balance_current'] ?? 0, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Transactions Card -->
                            <div class="bg-white dark:bg-gray-700 rounded-xl shadow-md overflow-hidden insights-card">
                                <div class="bg-green-500 px-4 py-2">
                                    <h3 class="text-white font-medium">Recent Transactions</h3>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-4">
                                        @php
                                            $transactions = $financialData['recent_transactions'] ?? [];
                                            $transactions = $transactions instanceof \Illuminate\Support\Collection ? $transactions->toArray() : $transactions;
                                            $recentTransactions = array_slice($transactions, 0, 5);
                                        @endphp
                                        @forelse($recentTransactions as $transaction)
                                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-600 pb-3 last:border-0 last:pb-0">
                                                <div class="flex items-center">
                                                    <div class="bg-{{ ($transaction['amount'] ?? 0) >= 0 ? 'green' : 'red' }}-100 dark:bg-{{ ($transaction['amount'] ?? 0) >= 0 ? 'green' : 'red' }}-900 rounded-full p-2 mr-3">
                                                        <i class="fas fa-{{ ($transaction['amount'] ?? 0) >= 0 ? 'arrow-down' : 'arrow-up' }} text-{{ ($transaction['amount'] ?? 0) >= 0 ? 'green' : 'red' }}-500 dark:text-{{ ($transaction['amount'] ?? 0) >= 0 ? 'green' : 'red' }}-400"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $transaction['description'] ?? 'No description' }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ \Carbon\Carbon::parse($transaction['date'] ?? now())->format('M d, Y') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-medium px-2 py-1 rounded-full {{ ($transaction['amount'] ?? 0) >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                    ${{ number_format(abs($transaction['amount'] ?? 0), 2) }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="text-center py-4">
                                                <i class="fas fa-receipt text-gray-300 dark:text-gray-600 text-4xl mb-2"></i>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">No recent transactions</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Insights Button -->
                        <div class="flex justify-center mb-8">
                            <form action="{{ route('ai.insights') }}" method="GET">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 border border-transparent rounded-lg font-semibold text-white shadow-sm hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150 transform hover:scale-105">
                                    <i class="fas fa-brain mr-2 text-lg"></i>
                                    Generate Financial Insights
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Results Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl">
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                            <h2 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-lightbulb mr-2 text-yellow-300"></i>
                                AI Analysis Results
                            </h2>
                        </div>
                        <div class="p-6">
                            @if(session('loading'))
                                <div id="loading" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500 mb-4"></div>
                                    <p class="text-lg text-gray-600 dark:text-gray-300 font-medium">Analyzing your financial data...</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">This may take a few moments</p>
                                </div>
                            @endif
                            
                            @if(session('error'))
                                <div id="error" class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-700 p-4 mb-6 rounded-lg shadow-sm">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-circle text-red-500 dark:text-red-400 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Error Generating Insights</h3>
                                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                                <p>{{ session('error') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div id="results" class="space-y-6">
                                @if(session('insights') && is_array(session('insights')))
                                    <div class="text-center mb-8">
                                        <div class="inline-block bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full px-4 py-1.5 text-sm font-medium mb-3">
                                            <i class="fas fa-check-circle mr-1.5"></i> Analysis Complete
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Your Financial Insights</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-2xl mx-auto">
                                            Based on your transaction history and account data, we've generated the following insights to help you manage your finances better.
                                        </p>
                                    </div>
                                    <div class="space-y-5">
                                        @foreach(session('insights') as $index => $insight)
                                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-100 dark:border-blue-800 overflow-hidden insights-card transform transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                                                <div class="p-5">
                                                    <div class="flex items-start">
                                                        <div class="flex-shrink-0 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full p-2.5 mr-4 shadow-md">
                                                            <span class="flex items-center justify-center h-6 w-6 text-white font-bold">{{ $index + 1 }}</span>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-800 dark:text-gray-200 text-base leading-relaxed">{{ $insight }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(session('raw'))
                                    <div class="text-center mb-8">
                                        <div class="inline-block bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full px-4 py-1.5 text-sm font-medium mb-3">
                                            <i class="fas fa-check-circle mr-1.5"></i> Analysis Complete
                                        </div>
                                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Analysis Results</h3>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                                        <div class="prose dark:prose-invert max-w-none">
                                            {!! session('raw') !!}
                                        </div>
                                    </div>
                                @elseif(!$apiKeyConfigured || !$hasData)
                                    <div class="text-center py-16">
                                        <div class="bg-gray-100 dark:bg-gray-800 inline-block rounded-full p-6 mb-6 shadow-inner">
                                            <i class="fas fa-chart-line text-gray-400 dark:text-gray-500 text-5xl"></i>
                                        </div>
                                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-3">No Insights Available Yet</h3>
                                        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                            @if(!$apiKeyConfigured)
                                                Configure your Gemini API key in the <span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">.env</span> file to start generating financial insights.
                                            @elseif(!$hasData)
                                                Connect your bank accounts and import transactions to get personalized financial insights.
                                            @else
                                                Click the "Generate Financial Insights" button above to analyze your data.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .insights-card {
        position: relative;
        overflow: hidden;
    }
    
    .insights-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
        transform: translateX(-100%);
        transition: transform 0.8s;
    }
    
    .insights-card:hover::before {
        transform: translateX(100%);
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
        }
    }
    
    .insights-card:hover .flex-shrink-0 {
        animation: pulse 1.5s infinite;
    }
</style>
@endpush