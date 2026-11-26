<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $product->name }} — Stock History</h2>
            <a href="{{ route('stock.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Back to movements</a>
        </div>
    </x-slot>

    <div class="mb-6 flex flex-wrap items-center gap-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div>
            <p class="text-xs font-medium text-gray-500">Current Stock</p>
            <p class="text-2xl font-bold text-gray-900">{{ $product->stock_quantity }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500">Reorder Level</p>
            <p class="text-2xl font-bold text-gray-900">{{ $product->reorder_level }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500">SKU</p>
            <p class="text-sm font-medium text-gray-900">{{ $product->sku }}</p>
        </div>
    </div>

    @php
        $typeBadge = [
            'in' => 'bg-emerald-100 text-emerald-800',
            'out' => 'bg-red-100 text-red-800',
            'adjustment' => 'bg-amber-100 text-amber-800',
            'sale' => 'bg-blue-100 text-blue-800',
            'return' => 'bg-purple-100 text-purple-800',
        ];
    @endphp

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typeBadge[$movement->type] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($movement->type) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold {{ $movement->quantity >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->reason ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No movements for this product yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $movements->links() }}
    </div>
</x-app-layout>
