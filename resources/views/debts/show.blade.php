<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Debt — {{ $debt->customer?->name ?? 'Customer' }}</h2>
            <a href="{{ route('debts.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Back to Debts</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: summary + payment history --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Original</p><p class="mt-1 text-lg font-bold">KSh {{ number_format($debt->original_amount, 2) }}</p></div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Paid</p><p class="mt-1 text-lg font-bold text-emerald-700">KSh {{ number_format($debt->amount_paid, 2) }}</p></div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Balance</p><p class="mt-1 text-lg font-bold text-red-700">KSh {{ number_format($debt->balance, 2) }}</p></div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Status</p><p class="mt-1 text-lg font-bold capitalize">{{ $debt->status }}</p></div>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4"><h3 class="text-base font-semibold text-gray-900">Payment History</h3></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Method</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($debt->payments as $payment)
                                <tr>
                                    <td class="px-5 py-3 text-sm text-gray-500">{{ $payment->created_at->format('d M Y, H:i') }}</td>
                                    <td class="px-5 py-3 text-sm font-medium text-emerald-700">KSh {{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-700">{{ ucfirst($payment->payment_method) }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-500">{{ $payment->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Record payment form --}}
            @if ($debt->balance > 0)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Record Payment</h3>
                    <form method="POST" action="{{ route('debts.payments.store', $debt) }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @csrf
                        <div>
                            <x-input-label for="amount" value="Amount (max KSh {{ number_format($debt->balance, 2) }})" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" max="{{ $debt->balance }}" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="payment_method" value="Payment Method" />
                            <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="cash">Cash</option>
                                <option value="mpesa">M-Pesa</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="notes" value="Notes" />
                            <x-text-input id="notes" name="notes" class="mt-1 block w-full" placeholder="e.g. M-Pesa transaction code" />
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-primary-button>Record Payment</x-primary-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- Right: customer + sale + SMS --}}
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Customer</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd>{{ $debt->customer?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $debt->customer?->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Credit Balance</dt><dd class="font-semibold text-red-600">KSh {{ number_format($debt->customer?->credit_balance ?? 0, 2) }}</dd></div>
                </dl>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Original Sale</h3>
                @if ($debt->sale)
                    <p class="mt-2 text-sm text-gray-700">Invoice: <span class="font-medium">{{ $debt->sale->invoice_number }}</span></p>
                    <p class="text-sm text-gray-500">{{ $debt->sale->created_at->format('d M Y, H:i') }}</p>
                    <a href="{{ route('sales.receipt', $debt->sale) }}" class="mt-3 inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">View Receipt</a>
                @else
                    <p class="mt-2 text-sm text-gray-500">No linked sale.</p>
                @endif
            </div>

            @if ($debt->balance > 0 && $debt->customer?->phone)
                @php $smsPhone = preg_replace('/^0/', '+254', $debt->customer->phone); $smsBody = urlencode("Dear {$debt->customer->name}, your outstanding balance with us is KSh {$debt->balance}. Kindly make payment. Thank you."); @endphp
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Follow Up</h3>
                    <p class="mt-1 text-sm text-gray-500">Send a reminder via the customer's SMS app.</p>
                    <a href="sms:{{ $smsPhone }}?body={{ $smsBody }}" class="mt-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Send Reminder</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
