<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Debtors Aging Report</h2>
            <a href="{{ route('debts.aging.export') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
        </div>
    </x-slot>

    @php
        $totals = ['current' => 0, 'd31_60' => 0, 'd61_90' => 0, 'd90_plus' => 0, 'total' => 0];
        foreach ($report as $row) {
            $totals['current'] += $row['current'];
            $totals['d31_60'] += $row['d31_60'];
            $totals['d61_90'] += $row['d61_90'];
            $totals['d90_plus'] += $row['d90_plus'];
            $totals['total'] += $row['total'];
        }
    @endphp

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Current (0-30)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">31-60 days</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">61-90 days</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">90+ days</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($report as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['customer'] }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['current'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['d31_60'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($row['d61_90'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-red-700">{{ number_format($row['d90_plus'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">KSh {{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No outstanding debts.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">Total</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">{{ number_format($totals['current'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">{{ number_format($totals['d31_60'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">{{ number_format($totals['d61_90'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-red-700">{{ number_format($totals['d90_plus'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">KSh {{ number_format($totals['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
