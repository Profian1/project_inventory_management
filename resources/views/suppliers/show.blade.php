<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $supplier->name }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Edit</a>
                <a href="{{ route('suppliers.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Details</h3>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $supplier->phone }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $supplier->email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd>{{ $supplier->address ?? '—' }}</dd></div>
            </dl>
            @if ($supplier->notes)
                <p class="mt-4 rounded-md bg-gray-50 p-3 text-sm text-gray-600">{{ $supplier->notes }}</p>
            @endif
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Products Supplied ({{ $products->count() }})</h3></div>
            <div class="divide-y divide-gray-200">
                @forelse ($products as $product)
                    <a href="{{ route('products.show', $product) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                        <span class="text-sm font-medium text-gray-900">{{ $product->name }}</span>
                        <span class="text-sm text-gray-500">KSh {{ number_format($product->buying_price, 2) }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500">No products linked to this supplier.</p>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Recent Stock-ins</h3></div>
            <div class="divide-y divide-gray-200">
                @forelse ($stockIns as $movement)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $movement->product?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $movement->created_at->format('d M Y') }} · {{ $movement->reason ?? '—' }}</p>
                        </div>
                        <span class="text-sm font-semibold text-emerald-700">+{{ $movement->quantity }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500">No stock-ins from this supplier yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
