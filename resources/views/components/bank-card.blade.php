@props(['bank'])

<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-gray-100 rounded-md p-3">
                <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l18 0M3 12h18M3 18h12"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $bank->institution_name }}
                    </dt>
                    <dd class="flex items-baseline">
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $bank->account_name }}
                        </div>
                    </dd>
                    <dd class="text-sm text-gray-500">
                        {{ ucfirst($bank->account_type) }} &bull; ••••{{ $bank->account_mask }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="bg-gray-50 px-4 py-4 sm:px-6">
        <div class="flex justify-between">
            <a href="{{ route('banks.show', $bank) }}" class="text-sm font-medium text-blue-600 hover:text-blue-900">
                View Details
            </a>
            <form action="{{ route('banks.destroy', $bank) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this bank account?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-900">
                    Remove
                </button>
            </form>
        </div>
    </div>
</div>
