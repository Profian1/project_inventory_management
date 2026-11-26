<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Suppliers</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('suppliers.report') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Purchases Report</a>
                <a href="{{ route('suppliers.create') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Add Supplier</a>
            </div>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('suppliers.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone or email…" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:max-w-md" />
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Search</button>
        <a href="{{ route('suppliers.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">Reset</a>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Products Supplied</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $supplier->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $supplier->phone }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $supplier->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $supplier->products_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View</a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Edit</a>
                                    @if (Auth::user()->isAdmin())
                                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $suppliers->links() }}
    </div>
</x-app-layout>
