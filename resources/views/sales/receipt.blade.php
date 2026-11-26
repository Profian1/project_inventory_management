@php
    $businessName = \App\Models\Setting::getValue('business_name', 'Balsheet');
    $businessAddress = \App\Models\Setting::getValue('business_address', '');
    $businessPhone = \App\Models\Setting::getValue('business_phone', '');
    $receiptFooter = \App\Models\Setting::getValue('receipt_footer', 'Thank you for shopping with us!');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Receipt</h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Print</button>
                <a href="{{ route('sales.create') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">New Sale</a>
            </div>
        </div>
    </x-slot>

    <div id="receipt" class="mx-auto max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="text-center">
            <h1 class="text-xl font-bold text-gray-900">{{ $businessName }}</h1>
            @if ($businessAddress)
                <p class="text-sm text-gray-500">{{ $businessAddress }}</p>
            @endif
            @if ($businessPhone)
                <p class="text-sm text-gray-500">{{ $businessPhone }}</p>
            @endif
        </div>

        <div class="mt-4 space-y-1 border-t border-dashed border-gray-300 pt-4 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Invoice</span><span class="font-medium">{{ $sale->invoice_number }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Date</span><span>{{ $sale->created_at->format('d M Y, H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Cashier</span><span>{{ $sale->user?->name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Customer</span><span>{{ $sale->customer?->name ?? 'Walk-in' }}</span></div>
        </div>

        <table class="mt-4 w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-gray-500">
                    <th class="py-1">Item</th>
                    <th class="py-1 text-center">Qty</th>
                    <th class="py-1 text-right">Price</th>
                    <th class="py-1 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr class="border-t border-gray-100">
                        <td class="py-1.5">{{ $item->product_name }}</td>
                        <td class="py-1.5 text-center">{{ $item->quantity }}</td>
                        <td class="py-1.5 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-1.5 text-right">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 space-y-1 border-t border-dashed border-gray-300 pt-4 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>KSh {{ number_format($sale->subtotal, 2) }}</span></div>
            @if ($sale->discount > 0)
                <div class="flex justify-between"><span class="text-gray-500">Discount</span><span>-KSh {{ number_format($sale->discount, 2) }}</span></div>
            @endif
            @if ($sale->vat_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">VAT (16%)</span><span>KSh {{ number_format($sale->vat_amount, 2) }}</span></div>
            @endif
            <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold text-gray-900"><span>Total</span><span>KSh {{ number_format($sale->total, 2) }}</span></div>
            <div class="flex justify-between pt-1"><span class="text-gray-500">Payment</span><span class="font-medium">{{ ucfirst($sale->payment_method) }}</span></div>
            @if ($sale->payment_method === 'cash')
                <div class="flex justify-between"><span class="text-gray-500">Amount Paid</span><span>KSh {{ number_format($sale->amount_paid, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Change</span><span>KSh {{ number_format($sale->change_given, 2) }}</span></div>
            @endif
        </div>

        <div class="mt-4 border-t border-dashed border-gray-300 pt-4 text-center text-sm text-gray-500">
            <p>{{ $receiptFooter }}</p>
        </div>
    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            #receipt, #receipt * { visibility: visible; }
            #receipt { position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; }
        }
    </style>
</x-app-layout>
