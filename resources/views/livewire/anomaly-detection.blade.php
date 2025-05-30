<div>
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">AI-Assisted Anomaly Detection</h3>
            <button 
                wire:click="refreshData"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
        <p class="text-gray-600 dark:text-gray-400">
            Our AI system analyzes your transaction history to identify unusual patterns that might indicate 
            fraudulent activity or unexpected spending. Review the flagged transactions below and mark them 
            as legitimate or suspicious.
        </p>
    </div>

    @if($loading)
        <div class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>
    @elseif($anomalies->isEmpty())
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">No anomalies detected</h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        <p>Our AI system hasn't detected any unusual transactions in your recent history. We'll continue monitoring your account for any suspicious activity.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">
                Unusual Transactions ({{ count($anomalies) }})
            </h4>
            
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Attention needed</h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>Our AI system has identified {{ count($anomalies) }} unusual transactions that may require your attention. Please review them and mark them as legitimate or suspicious.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Merchant</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">AI Analysis</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($anomalies as $transaction)
                        <tr id="transaction-{{ $transaction->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $transaction->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $transaction->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $transaction->category }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                ${{ number_format(abs($transaction->amount), 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                                    <p class="text-blue-800 dark:text-blue-200">{{ $transaction->anomaly_explanation }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button 
                                        wire:click="openFeedbackModal({{ $transaction->id }}, true)"
                                        class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-3 py-1 rounded-full text-xs font-medium hover:bg-green-200 dark:hover:bg-green-800 transition-colors"
                                    >
                                        Legitimate
                                    </button>
                                    <button 
                                        wire:click="openFeedbackModal({{ $transaction->id }}, false)"
                                        class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 px-3 py-1 rounded-full text-xs font-medium hover:bg-red-200 dark:hover:bg-red-800 transition-colors"
                                    >
                                        Suspicious
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Feedback Modal -->
    <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        x-data="{ show: @entangle('showFeedbackModal') }"
        x-show="show"
        x-cloak
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ $isLegitimate ? 'Confirm Legitimate Transaction' : 'Report Suspicious Transaction' }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ $isLegitimate 
                        ? 'Please provide any additional information about why this transaction is legitimate.' 
                        : 'Please provide any details about why you believe this transaction is suspicious.' 
                    }}
                </p>
                <textarea 
                    wire:model.defer="feedback" 
                    class="w-full px-3 py-2 text-gray-700 dark:text-gray-300 border rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                    rows="4" 
                    placeholder="Optional feedback..."
                ></textarea>
                <div class="mt-4 flex justify-end space-x-3">
                    <button 
                        wire:click="closeFeedbackModal" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="submitFeedback" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                    >
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div
        x-data="{ 
            messages: [],
            remove(message) {
                this.messages.splice(this.messages.indexOf(message), 1)
            },
        }"
        @transaction-reviewed="let msg = $event.detail; messages.push(msg); setTimeout(() => { remove(msg) }, 3000)"
        @data-refreshed="let msg = $event.detail; messages.push(msg); setTimeout(() => { remove(msg) }, 3000)"
        class="fixed inset-0 z-50 flex flex-col items-end justify-start p-4 space-y-4 pointer-events-none"
    >
        <template x-for="(message, messageIndex) in messages" :key="messageIndex">
            <div
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-10"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-10"
                class="w-72 bg-white dark:bg-gray-800 rounded-lg shadow-lg pointer-events-auto"
            >
                <div class="p-4 flex items-start">
                    <div class="flex-shrink-0" x-show="message.type === 'success'">
                        <svg class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-shrink-0" x-show="message.type === 'error'">
                        <svg class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="message.message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="remove(message)" class="inline-flex text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
