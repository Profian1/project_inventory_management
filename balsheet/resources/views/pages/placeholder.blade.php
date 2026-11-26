<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $title }}</h2>
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-md bg-gray-50 p-4 text-sm text-gray-500">
                    This module is under construction and will be completed in a later phase of the project.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
