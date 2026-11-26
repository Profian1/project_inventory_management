<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
    </x-slot>

    {{-- Top row: key business metrics ------------------------------------}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Today's Sales" :value="'KSh ' . number_format($todaySales, 2)">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card label="Today's Profit" :value="'KSh ' . number_format($todayProfit, 2)">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card label="Transactions Today" :value="$todayTransactions">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card label="Low Stock Items" :value="$lowStockCount" icon-bg="bg-amber-100 text-amber-600">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Second row: sales + profit charts ----------------------------------}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Sales — Last 7 Days</h3>
            <div class="mt-4 h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Profit — Last 30 Days</h3>
            <div class="mt-4 h-64">
                <canvas id="profitChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Third row: low stock + recent sales ---------------------------------}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Low Stock Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stock</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reorder</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($lowStockProducts as $product)
                            <tr>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">{{ $product->stock_quantity }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">{{ $product->reorder_level }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('stock.index') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-700">Restock</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">No low stock items.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Recent Sales</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Invoice</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Payment</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $paymentBadge = [
                                'cash' => 'bg-emerald-100 text-emerald-800',
                                'mpesa' => 'bg-teal-100 text-teal-800',
                                'credit' => 'bg-amber-100 text-amber-800',
                            ];
                        @endphp
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">KSh {{ number_format($sale->total, 2) }}</td>
                                <td class="px-5 py-3 text-sm">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $paymentBadge[$sale->payment_method] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($sale->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $sale->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500">No sales yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Fourth row: top products + debts summary ----------------------------}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Top 5 Selling Products (This Month)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Qty Sold</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($topProducts as $product)
                            <tr>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $product['product_name'] }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">{{ $product['qty_sold'] }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">KSh {{ number_format($product['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-sm text-gray-500">No sales this month yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Outstanding Debts</h3>
            <p class="mt-4 text-3xl font-bold text-gray-900">KSh {{ number_format($totalDebt, 2) }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $debtorsCount }} {{ \Illuminate\Support\Str::plural('debtor', $debtorsCount) }} with an outstanding balance</p>
            <a href="{{ route('debts.index') }}" class="mt-6 inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                View all debts
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const emerald = 'rgb(16, 185, 129)';

        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: @json($sales7Days['labels']),
                datasets: [{
                    label: 'Sales (KSh)',
                    data: @json($sales7Days['values']),
                    backgroundColor: 'rgba(16, 185, 129, 0.6)',
                    borderColor: emerald,
                    borderWidth: 1,
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });

        new Chart(document.getElementById('profitChart'), {
            type: 'line',
            data: {
                labels: @json($profit30Days['labels']),
                datasets: [{
                    label: 'Profit (KSh)',
                    data: @json($profit30Days['values']),
                    borderColor: emerald,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    </script>
</x-app-layout>
