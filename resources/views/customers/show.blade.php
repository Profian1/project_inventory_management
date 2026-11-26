<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $customer->name }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Edit</a>
                <a href="{{ route('customers.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Customer info --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Details</h3>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $customer->phone }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $customer->email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd>{{ $customer->address ?? '—' }}</dd></div>
                <div class="flex justify-between border-t border-gray-200 pt-2"><dt class="text-gray-500">Credit Balance</dt><dd class="font-semibold {{ $customer->credit_balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">KSh {{ number_format($customer->credit_balance, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total Spent</dt><dd class="font-semibold">KSh {{ number_format($totalSpent, 2) }}</dd></div>
            </dl>
            @if ($customer->notes)
                <p class="mt-4 rounded-md bg-gray-50 p-3 text-sm text-gray-600">{{ $customer->notes }}</p>
            @endif
        </div>

        {{-- Purchase history --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Recent Purchases</h3></div>
            <div class="divide-y divide-gray-200">
                @forelse ($sales as $sale)
                    <a href="{{ route('sales.show', $sale) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</p>
                            <p class="text-xs text-gray-500">{{ $sale->created_at->format('d M Y') }} · {{ ucfirst($sale->payment_method) }}</p>
                        </div>
                        <span class="text-sm font-medium text-gray-700">KSh {{ number_format($sale->total, 2) }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500">No purchases yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Debt history --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Debt History</h3></div>
            <div class="divide-y divide-gray-200">
                @forelse ($debts as $debt)
                    <a href="{{ route('debts.show', $debt) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                        <div>
                            <p class="text-sm font-medium text-gray-900">KSh {{ number_format($debt->original_amount, 2) }}</p>
                            <p class="text-xs text-gray-500">{{ $debt->created_at->format('d M Y') }} · {{ ucfirst($debt->status) }}</p>
                        </div>
                        <span class="text-sm font-medium {{ $debt->balance > 0 ? 'text-red-600' : 'text-emerald-700' }}">KSh {{ number_format($debt->balance, 2) }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500">No debts.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
