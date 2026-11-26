<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Products</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.export', request()->query()) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Export CSV</a>
                <a href="{{ route('products.create') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Add Product</a>
            </div>
        </div>
    </x-slot>

    {{-- Filters --}}
    <form method="GET" action="{{ route('products.index') }}" class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU, barcode…" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div>
                <select name="category_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="stock" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All stock</option>
                    <option value="in" @selected(request('stock') === 'in')>In stock</option>
                    <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                    <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
                </select>
            </div>
            <div>
                <select name="supplier_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
            </div>
        </div>

        @if (Auth::user()->isAdmin())
            <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="trash" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(request('trash') === '1') />
                Show trashed products
            </label>
        @endif
    </form>

    @php
        $isTrash = request('trash') === '1';
        $stockBadge = function ($product) {
            if ($product->stock_quantity == 0) {
                return 'bg-red-100 text-red-800';
            }
            if ($product->stock_quantity <= $product->reorder_level) {
                return 'bg-amber-100 text-amber-800';
            }
            return 'bg-emerald-100 text-emerald-800';
        };
        $stockLabel = function ($product) {
            if ($product->stock_quantity == 0) {
                return 'Out';
            }
            if ($product->stock_quantity <= $product->reorder_level) {
                return 'Low';
            }
            return 'In';
        };
    @endphp

    <form method="POST" action="{{ route('products.bulk-action') }}" x-data>
        @csrf
        <input type="hidden" name="action" x-ref="action" />

        <div class="mb-3 flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-500">Bulk:</span>
            @if (! $isTrash)
                <button type="button" @click="$refs.action.value = 'activate'; $el.form.submit()" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Activate</button>
                <button type="button" @click="$refs.action.value = 'deactivate'; $el.form.submit()" class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Deactivate</button>
                @if (Auth::user()->isAdmin())
                    <button type="button" @click="$refs.action.value = 'delete'; $el.form.submit()" class="rounded-md border border-red-300 bg-white px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">Delete</button>
                @endif
            @endif
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left"><input type="checkbox" id="select-all" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" /></th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Image</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">Name @if (request('sort') === 'name'){{ request('direction') === 'asc' ? '▲' : '▼' }}@endif</a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Buying</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'selling_price', 'direction' => request('sort') === 'selling_price' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">Selling @if (request('sort') === 'selling_price'){{ request('direction') === 'asc' ? '▲' : '▼' }}@endif</a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'margin', 'direction' => request('sort') === 'margin' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">Margin % @if (request('sort') === 'margin'){{ request('direction') === 'asc' ? '▲' : '▼' }}@endif</a>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'stock_quantity', 'direction' => request('sort') === 'stock_quantity' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">Stock @if (request('sort') === 'stock_quantity'){{ request('direction') === 'asc' ? '▲' : '▼' }}@endif</a>
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $product->id }}" class="row-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" /></td>
                                <td class="px-4 py-3">
                                    @if ($product->image_path)
                                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-md object-cover" />
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-gray-100 text-gray-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->sku }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($product->buying_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">KSh {{ number_format($product->selling_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $product->margin_percentage }}%</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $stockBadge($product) }}">
                                        {{ $stockLabel($product) }} · {{ $product->stock_quantity }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($isTrash)
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('products.restore', $product->id) }}">
                                                @csrf
                                                <button type="submit" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Restore</button>
                                            </form>
                                            <form method="POST" action="{{ route('products.force-delete', $product->id) }}" onsubmit="return confirm('Permanently delete this product? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Delete forever</button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View</a>
                                            <a href="{{ route('products.edit', $product) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Edit</a>
                                            @if (Auth::user()->isAdmin())
                                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Move this product to trash?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-sm text-gray-500">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(function (cb) {
                cb.checked = this.checked;
            }, this);
        });
    </script>
</x-app-layout>
