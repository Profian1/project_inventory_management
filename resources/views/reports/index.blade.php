<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Reports</h2>
    </x-slot>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('reports.daily-sales') }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-emerald-400">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
            </span>
            <h3 class="mt-3 text-base font-semibold text-gray-900">Daily Sales</h3>
            <p class="mt-1 text-sm text-gray-500">Sales, profit, VAT and payment breakdown.</p>
        </a>

        <a href="{{ route('reports.low-stock') }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-emerald-400">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4" /></svg>
            </span>
            <h3 class="mt-3 text-base font-semibold text-gray-900">Low Stock</h3>
            <p class="mt-1 text-sm text-gray-500">Inventory levels, stock value and reorder alerts.</p>
        </a>

        <a href="{{ route('reports.profit-margin') }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-emerald-400">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
            </span>
            <h3 class="mt-3 text-base font-semibold text-gray-900">Profit &amp; Margin</h3>
            <p class="mt-1 text-sm text-gray-500">Revenue, cost and profit by product.</p>
        </a>

        <a href="{{ route('reports.cashier') }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-emerald-400">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
            </span>
            <h3 class="mt-3 text-base font-semibold text-gray-900">Sales by Cashier</h3>
            <p class="mt-1 text-sm text-gray-500">Performance by staff member.</p>
        </a>

        <a href="{{ route('debts.aging') }}" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-emerald-400">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
            </span>
            <h3 class="mt-3 text-base font-semibold text-gray-900">Debtors Aging</h3>
            <p class="mt-1 text-sm text-gray-500">Outstanding debts by age bucket.</p>
        </a>
    </div>
</x-app-layout>
