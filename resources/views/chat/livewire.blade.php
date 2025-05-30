<x-layouts.app :title="__('Chat')">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Financial Assistant') }}
            </h2>
            <div class="flex space-x-2">
                <span class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                    <i class="fas fa-robot mr-1"></i> Powered by Gemini AI
                </span>
            </div>
        </div>
    </x-slot>

    <livewire:chat />
</x-layouts.app>
