<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Insights</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h1 class="text-2xl font-bold mb-6">AI Insights</h1>
            
            <div class="mb-8 p-4 bg-blue-50 rounded-lg">
                <h2 class="text-lg font-semibold mb-2">API Status</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p><span class="font-medium">API Key Configured:</span> 
                            @if($apiKeyConfigured)
                                <span class="text-green-600">Yes ({{ $apiKeyLength }} chars)</span>
                            @else
                                <span class="text-red-600">No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p><span class="font-medium">Real Data Available:</span> 
                            {{ $hasRealData ? 'Yes' : 'No' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border">
                <h2 class="text-lg font-semibold mb-4">Your Data</h2>
                @if($hasRealData)
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-medium">Summary</h3>
                            <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ json_encode($realData['summary'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div>
                            <h3 class="font-medium">Accounts ({{ count($realData['accounts'] ?? []) }})</h3>
                            <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ json_encode($realData['accounts'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div>
                            <h3 class="font-medium">Recent Transactions ({{ count($realData['recent_transactions'] ?? []) }})</h3>
                            @php
                                $transactions = $realData['recent_transactions'] ?? [];
                                $transactions = $transactions instanceof \Illuminate\Support\Collection ? $transactions->toArray() : $transactions;
                                $recentTransactions = array_slice($transactions, 0, 5);
                            @endphp
                            <pre class="text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ json_encode($recentTransactions, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div>
                            <button onclick="generateInsights()" 
                                    class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition-colors">
                                Generate Insights
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-gray-600">No transaction data available. Please import transactions first.</p>
                @endif
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Results</h2>
            <div id="loading" class="hidden text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <p>Generating insights...</p>
            </div>
            <div id="error" class="hidden bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p id="errorMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div id="results" class="space-y-4">
                <!-- Results will be displayed here -->
            </div>
        </div>
    </div>

    <script>
        async function generateInsights() {
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');
            const errorMessage = document.getElementById('errorMessage');
            const results = document.getElementById('results');
            
            // Reset UI
            loading.classList.remove('hidden');
            error.classList.add('hidden');
            results.innerHTML = '';
            
            try {
                // Use real data
                const response = await axios.get('{{ route("ai.insights") }}');
                displayResults(response.data);
            } catch (err) {
                console.error('Error:', err);
                errorMessage.textContent = err.response?.data?.error || err.message || 'An unknown error occurred';
                error.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
            }
        }
        
        function displayResults(data) {
            const results = document.getElementById('results');
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            if (data.insights && data.insights.length > 0) {
                results.innerHTML = data.insights.map(insight => `
                    <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                        <p class="text-gray-800">${insight}</p>
                    </div>
                `).join('');
            } else if (data.raw) {
                results.innerHTML = `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <pre class="whitespace-pre-wrap">${data.raw}</pre>
                    </div>
                `;
            } else {
                throw new Error('No insights available');
            }
        }
    </script>
</body>
</html>