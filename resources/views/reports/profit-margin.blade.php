<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Profit &amp; Margin Report</h2>
            <div class="flex items-center gap-2 print:hidden">
                <a href="{{ route('reports.profit-margin.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Print</button>
            </div>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('reports.profit-margin') }}" class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end print:hidden">
        <div>
            <label class="text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Category</label>
            <select name="category_id" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Apply</button>
    </form>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Revenue" :value="'KSh ' . number_format($totalRevenue, 2)">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Total Cost" :value="'KSh ' . number_format($totalCost, 2)" icon-bg="bg-red-100 text-red-600">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Gross Profit" :value="'KSh ' . number_format($totalProfit, 2)" icon-bg="bg-emerald-100 text-emerald-600">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Avg Margin" :value="$avgMargin . '%'">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /></svg></x-slot>
        </x-stat-card>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Top 10 Products by Profit</h3></div>
        <div class="p-5"><div class="h-64"><canvas id="profitChart"></canvas></div></div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Profit by Product</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Qty Sold</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Revenue</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cost</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Profit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item['product'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item['qty'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($item['revenue'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($item['cost'], 2) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-emerald-700">KSh {{ number_format($item['profit'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item['margin'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('profitChart'), {
            type: 'bar',
            data: { labels: @json(collect($top10)->pluck('product')->all()), datasets: [{ label: 'Profit (KSh)', data: @json(collect($top10)->pluck('profit')->all()), backgroundColor: 'rgba(16,185,129,0.6)', borderColor: 'rgb(16,185,129)', borderWidth: 1, borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    </script>
</x-app-layout>
