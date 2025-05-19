@props(['transactions'])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($transactions as $transaction)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $transaction->date->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $transaction->name }}</div>
                        @if ($transaction->channel === 'internal_transfer')
                            <div class="text-xs text-gray-500">
                                @php
                                    $meta = json_decode($transaction->payment_meta, true);
                                    $note = $meta['note'] ?? '';
                                @endphp
                                @if ($note)
                                    <span class="italic">"{{ $note }}"</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $transaction->bank->institution_name }} - {{ $transaction->bank->account_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->channel === 'plaid' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ $transaction->channel === 'plaid' ? 'Bank' : 'Transfer' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $transaction->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $transaction->amount < 0 ? '-' : '+' }}${{ number_format(abs($transaction->amount), 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>