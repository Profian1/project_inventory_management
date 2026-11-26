<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Daily Sales Report</h2>
            <div class="flex items-center gap-2 print:hidden">
                <a href="{{ route('reports.daily-sales.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Print</button>
            </div>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('reports.daily-sales') }}" class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end print:hidden">
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Sales" :value="'KSh ' . number_format($totalSales, 2)">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Transactions" :value="$transactionCount">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75z" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Avg Sale Value" :value="'KSh ' . number_format($avgSale, 2)">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot>
        </x-stat-card>
        <x-stat-card label="Total Profit" :value="'KSh ' . number_format($totalProfit, 2)" icon-bg="bg-emerald-100 text-emerald-600">
            <x-slot name="icon"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg></x-slot>
        </x-stat-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Daily Sales</h3>
            <div class="mt-4 h-64"><canvas id="dailyChart"></canvas></div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Payment Method Breakdown</h3>
            <div class="mt-4 h-64"><canvas id="paymentChart"></canvas></div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Daily Breakdown</h3></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"># Sales</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Gross Sales</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Profit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">VAT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($daily as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row['count'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">KSh {{ number_format($row['gross'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-emerald-700">KSh {{ number_format($row['profit'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($row['vat'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: { labels: @json(collect($daily)->pluck('date')->all()), datasets: [{ label: 'Gross Sales (KSh)', data: @json(collect($daily)->pluck('gross')->all()), backgroundColor: 'rgba(16,185,129,0.6)', borderColor: 'rgb(16,185,129)', borderWidth: 1, borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
        new Chart(document.getElementById('paymentChart'), {
            type: 'pie',
            data: { labels: @json(array_keys($byPayment)), datasets: [{ data: @json(array_values($byPayment)), backgroundColor: ['rgb(16,185,129)', 'rgb(13,148,136)', 'rgb(245,158,11)'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    </script>
</x-app-layout>
