<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Debts</h2>
            <a href="{{ route('debts.aging') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Aging Report</a>
        </div>
    </x-slot>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Owed" :value="'KSh ' . number_format($totalOwed, 2)">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card label="Overdue Amount" :value="'KSh ' . number_format($overdueAmount, 2)" icon-bg="bg-red-100 text-red-600">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card label="Debtors" :value="$debtorsCount">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
            </x-slot>
        </x-stat-card>
        <x-stat-card label="Collected This Month" :value="'KSh ' . number_format($collectedThisMonth, 2)" icon-bg="bg-emerald-100 text-emerald-600">
            <x-slot name="icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('debts.index') }}" class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-6">
            <div>
                <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All statuses</option>
                    @foreach (['unpaid', 'partial', 'paid'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
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
                <input type="date" name="from" value="{{ request('from') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div>
                <input type="date" name="to" value="{{ request('to') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="overdue" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(request('overdue')) />
                Overdue only
            </label>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('debts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
            </div>
        </div>
    </form>

    @php
        $statusBadge = [
            'unpaid' => 'bg-red-100 text-red-800',
            'partial' => 'bg-amber-100 text-amber-800',
            'paid' => 'bg-emerald-100 text-emerald-800',
        ];
    @endphp

    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Original</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Paid</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Balance</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Due Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($debts as $debt)
                        @php
                            $isOverdue = $debt->balance > 0 && $debt->due_date && $debt->due_date->isPast();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $debt->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $debt->sale?->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($debt->original_amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-emerald-700">KSh {{ number_format($debt->amount_paid, 2) }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-red-700">KSh {{ number_format($debt->balance, 2) }}</td>
                            <td class="px-4 py-3 text-sm {{ $isOverdue ? 'font-semibold text-red-600' : 'text-gray-500' }}">{{ $debt->due_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$debt->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($debt->status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('debts.show', $debt) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View</a>
                                    @if ($debt->balance > 0 && $debt->customer?->phone)
                                        @php $smsPhone = preg_replace('/^0/', '+254', $debt->customer->phone); $smsBody = urlencode("Dear {$debt->customer->name}, your outstanding balance is KSh {$debt->balance}. Kindly make payment. Thank you."); @endphp
                                        <a href="sms:{{ $smsPhone }}?body={{ $smsBody }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Remind</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">No debts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $debts->links() }}
    </div>
</x-app-layout>
