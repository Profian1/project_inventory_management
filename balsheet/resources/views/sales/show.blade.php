<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Sale {{ $sale->invoice_number }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.receipt', $sale) }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">View Receipt</a>
                <a href="{{ route('sales.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Qty</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Price</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($sale->items as $item)
                            <tr>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $item->product_name }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">KSh {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">KSh {{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Details</h3>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd>{{ $sale->created_at->format('d M Y, H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Cashier</dt><dd>{{ $sale->user?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Customer</dt><dd>{{ $sale->customer?->name ?? 'Walk-in' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>KSh {{ number_format($sale->subtotal, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd>KSh {{ number_format($sale->discount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">VAT</dt><dd>KSh {{ number_format($sale->vat_amount, 2) }}</dd></div>
                <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900"><dt>Total</dt><dd>KSh {{ number_format($sale->total, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Payment</dt><dd class="font-medium">{{ ucfirst($sale->payment_method) }}</dd></div>
                @if ($sale->payment_method === 'cash')
                    <div class="flex justify-between"><dt class="text-gray-500">Amount Paid</dt><dd>KSh {{ number_format($sale->amount_paid, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Change</dt><dd>KSh {{ number_format($sale->change_given, 2) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-medium capitalize">{{ $sale->status }}</dd></div>
            </dl>
        </div>
    </div>
</x-app-layout>
