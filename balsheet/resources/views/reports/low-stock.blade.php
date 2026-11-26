<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Low Stock / Inventory Report</h2>
            <div class="flex items-center gap-2 print:hidden">
                <a href="{{ route('reports.low-stock.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Print</button>
            </div>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('reports.low-stock') }}" class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end print:hidden">
        <div>
            <label class="text-xs font-medium text-gray-500">Category</label>
            <select name="category_id" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Supplier</label>
            <select name="supplier_id" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All suppliers</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Apply</button>
    </form>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-stat-card label="Total Products" :value="$totalProducts">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Stock Value (Buying)" :value="'KSh ' . number_format($totalBuyingValue, 2)">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Potential Revenue" :value="'KSh ' . number_format($totalSellingValue, 2)" icon-bg="bg-emerald-100 text-emerald-600">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Low Stock" :value="$lowStockCount" icon-bg="bg-amber-100 text-amber-600">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Out of Stock" :value="$outOfStockCount" icon-bg="bg-red-100 text-red-600">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg></x-slot>
        </x-stat-card>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Stock Value by Category</h3></div>
        <div class="p-5"><div class="h-64"><canvas id="stockChart"></canvas></div></div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Inventory</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reorder</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Buying Value</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Selling Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($products as $product)
                        @php
                            $status = $product['stock_quantity'] == 0 ? 'Out' : ($product['stock_quantity'] <= $product['reorder_level'] ? 'Low' : 'In');
                            $rowClass = $product['stock_quantity'] == 0 ? 'bg-red-50' : ($product['stock_quantity'] <= $product['reorder_level'] ? 'bg-amber-50' : '');
                            $badge = $product['stock_quantity'] == 0 ? 'bg-red-100 text-red-800' : ($product['stock_quantity'] <= $product['reorder_level'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $product['category'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $product['stock_quantity'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $product['reorder_level'] }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ $status }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($product['buying_value'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($product['selling_value'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No products.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('stockChart'), {
            type: 'bar',
            data: { labels: @json(array_keys($byCategory)), datasets: [{ label: 'Stock Value (KSh)', data: @json(array_values($byCategory)), backgroundColor: 'rgba(16,185,129,0.6)', borderColor: 'rgb(16,185,129)', borderWidth: 1, borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    </script>
</x-app-layout>
