<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Record Stock Movement</h2>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('stock.store') }}" x-data="{ type: '{{ old('type', $type) }}', mode: '{{ old('adjustment_mode', 'add_subtract') }}' }" class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            {{-- Movement type --}}
            <div>
                <span class="block text-sm font-medium text-gray-700">Movement Type</span>
                <div class="mt-2 flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="type" value="in" x-model="type" class="border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm">Stock In</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="type" value="out" x-model="type" class="border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm">Stock Out</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="type" value="adjustment" x-model="type" class="border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm">Adjustment</span>
                    </label>
                </div>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Adjustment mode (only for adjustment) --}}
            <div x-show="type === 'adjustment'" x-cloak>
                <span class="block text-sm font-medium text-gray-700">Adjustment Mode</span>
                <div class="mt-2 flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="adjustment_mode" value="add_subtract" x-model="mode" class="border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm">Add / Subtract</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="adjustment_mode" value="set" x-model="mode" class="border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm">Set to specific value</span>
                    </label>
                </div>
            </div>

            {{-- Product --}}
            <x-product-search name="product_id" label="Product" />

            {{-- Quantity --}}
            <div>
                <x-input-label for="quantity" value="Quantity" />
                <x-text-input id="quantity" name="quantity" type="number" step="1" class="mt-1 block w-full" :value="old('quantity')" required />
                <p x-show="type !== 'adjustment'" class="mt-1 text-xs text-gray-500">Enter a positive quantity.</p>
                <p x-show="type === 'adjustment' && mode === 'add_subtract'" class="mt-1 text-xs text-gray-500">Positive to add stock, negative to subtract.</p>
                <p x-show="type === 'adjustment' && mode === 'set'" class="mt-1 text-xs text-gray-500">Enter the new (target) stock quantity.</p>
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reason --}}
            <div>
                <x-input-label for="reason" value="Reason" />
                <x-text-input id="reason" name="reason" class="mt-1 block w-full" :value="old('reason')" placeholder="e.g. Restock from supplier, Damaged, Return" />
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reference --}}
            <div>
                <x-input-label for="reference" value="Reference (optional)" />
                <x-text-input id="reference" name="reference" class="mt-1 block w-full" :value="old('reference')" placeholder="e.g. supplier invoice #" />
                @error('reference')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Record Movement</x-primary-button>
                <a href="{{ route('stock.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
