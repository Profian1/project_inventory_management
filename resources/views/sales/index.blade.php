<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Sales History</h2>
            <a href="{{ route('sales.create') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New Sale</a>
        </div>
    </x-slot>

    {{-- Filters --}}
    <form method="GET" action="{{ route('sales.index') }}" class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-6">
            <div>
                <input type="date" name="from" value="{{ request('from') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div>
                <input type="date" name="to" value="{{ request('to') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div>
                <select name="payment_method" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All payments</option>
                    @foreach (['cash', 'mpesa', 'credit'] as $method)
                        <option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ ucfirst($method) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="customer_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All customers</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="user_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All cashiers</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('sales.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
            </div>
        </div>
    </form>

    @php
        $paymentBadge = [
            'cash' => 'bg-emerald-100 text-emerald-800',
            'mpesa' => 'bg-teal-100 text-teal-800',
            'credit' => 'bg-amber-100 text-amber-800',
        ];
        $statusBadge = [
            'completed' => 'bg-emerald-100 text-emerald-800',
            'pending' => 'bg-amber-100 text-amber-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
    @endphp

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $sale->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $sale->items->sum('quantity') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">KSh {{ number_format($sale->total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $paymentBadge[$sale->payment_method] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($sale->payment_method) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$sale->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($sale->status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View</a>
                                    <a href="{{ route('sales.receipt', $sale) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Receipt</a>
                                    @if (Auth::user()->isAdmin() && $sale->status !== 'cancelled')
                                        <form method="POST" action="{{ route('sales.cancel', $sale) }}" onsubmit="return confirm('Cancel this sale? Stock will be restored.');">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">No sales yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $sales->links() }}
    </div>
</x-app-layout>
