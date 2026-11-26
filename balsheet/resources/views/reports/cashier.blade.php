<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Sales by Cashier</h2>
            <div class="flex items-center gap-2 print:hidden">
                <a href="{{ route('reports.cashier.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Print</button>
            </div>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('reports.cashier') }}" class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end print:hidden">
        <div>
            <label class="text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
        </div>
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Apply</button>
    </form>

    <div class="mt-2 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Cashier Performance</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cashier</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"># Sales</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Total Sales</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Avg Sale</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row['count'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">KSh {{ number_format($row['total'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($row['avg'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-gray-900">Total Sales by Cashier</h3>
        <div class="mt-4 h-64"><canvas id="cashierChart"></canvas></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('cashierChart'), {
            type: 'bar',
            data: { labels: @json(collect($rows)->pluck('name')->all()), datasets: [{ label: 'Total Sales (KSh)', data: @json(collect($rows)->pluck('total')->all()), backgroundColor: 'rgba(16,185,129,0.6)', borderColor: 'rgb(16,185,129)', borderWidth: 1, borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    </script>
</x-app-layout>
