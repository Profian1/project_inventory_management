<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Stock Movements</h2>
            <a href="{{ route('stock.create') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Record Movement</a>
        </div>
    </x-slot>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Stock In (This Month)" :value="'+' . number_format($stockIn)">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card label="Stock Out (This Month)" :value="'-' . number_format($stockOut)" icon-bg="bg-red-100 text-red-600">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card label="Net Change" :value="($netChange >= 0 ? '+' : '') . number_format($netChange)" icon-bg="bg-gray-100 text-gray-600">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('stock.index') }}" class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-6">
            <div>
                <select name="product_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All products</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All types</option>
                    @foreach (['in', 'out', 'adjustment', 'sale', 'return'] as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="user_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from" value="{{ request('from') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div>
                <input type="date" name="to" value="{{ request('to') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('stock.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
            </div>
        </div>
    </form>

    @php
        $typeBadge = [
            'in' => 'bg-emerald-100 text-emerald-800',
            'out' => 'bg-red-100 text-red-800',
            'adjustment' => 'bg-amber-100 text-amber-800',
            'sale' => 'bg-blue-100 text-blue-800',
            'return' => 'bg-purple-100 text-purple-800',
        ];
    @endphp

    {{-- Movements table --}}
    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('stock.product', $movement->product) }}" class="hover:text-emerald-700">{{ $movement->product?->name ?? '—' }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typeBadge[$movement->type] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($movement->type) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold {{ $movement->quantity >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->reason ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No stock movements recorded yet.</td>
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
