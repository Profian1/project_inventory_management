<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Purchases by Supplier</h2>
            <a href="{{ route('suppliers.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Back to Suppliers</a>
        </div>
    </x-slot>

    @php
        $total = $report->sum('total_purchases');
    @endphp

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Supplier</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Total Purchases (Stock-in Value)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($report as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row->name }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">KSh {{ number_format($row->total_purchases, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-10 text-center text-sm text-gray-500">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">Total</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">KSh {{ number_format($total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
