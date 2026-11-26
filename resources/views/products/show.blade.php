<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $product->name }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('stock.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Adjust Stock</a>
                <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Edit</a>
                @if (Auth::user()->isAdmin())
                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Move this product to trash?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 shadow-sm hover:bg-red-50">Deactivate</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Product details --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex flex-col gap-6 sm:flex-row">
                <div class="shrink-0">
                    @if ($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-40 w-40 rounded-lg object-cover" />
                    @else
                        <div class="flex h-40 w-40 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                            <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $product->sku }}</span>
                        @if ($product->is_active)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Active</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Inactive</span>
                        @endif
                    </div>

                    <p class="mt-2 text-sm text-gray-600">{{ $product->description ?? 'No description.' }}</p>

                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Category</dt>
                            <dd class="text-sm text-gray-900">{{ $product->category?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Supplier</dt>
                            <dd class="text-sm text-gray-900">{{ $product->supplier?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Buying Price</dt>
                            <dd class="text-sm text-gray-900">KSh {{ number_format($product->buying_price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Selling Price</dt>
                            <dd class="text-sm text-gray-900">KSh {{ number_format($product->selling_price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Margin</dt>
                            <dd class="text-sm text-gray-900">KSh {{ number_format($product->margin, 2) }} ({{ $product->margin_percentage }}%)</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500">Stock</dt>
                            <dd class="text-sm text-gray-900">{{ $product->stock_quantity }} / reorder at {{ $product->reorder_level }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Barcode + QR --}}
            <div class="mt-6 grid grid-cols-1 gap-6 border-t border-gray-200 pt-6 sm:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Barcode</h3>
                    @if ($product->barcode)
                        <div class="mt-2">{!! DNS1D::getBarcodeHTML($product->barcode, 'C128') !!}</div>
                        <p class="mt-1 text-xs text-gray-500">{{ $product->barcode }}</p>
                    @else
                        <p class="mt-2 text-sm text-gray-500">No barcode assigned.</p>
                    @endif
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">QR Code</h3>
                    <div class="mt-2">{!! QrCode::size(140)->generate($product->sku) !!}</div>
                    <p class="mt-1 text-xs text-gray-500">{{ $product->sku }}</p>
                </div>
            </div>
        </div>

        {{-- Sales history (sidebar) --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Recent Sales</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse ($sales as $item)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $item->sale->invoice_number ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $item->sale?->created_at?->diffForHumans() ?? '—' }} · qty {{ $item->quantity }}</p>
                        </div>
                        <span class="text-sm font-medium text-gray-700">KSh {{ number_format($item->unit_price, 2) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500">No sales yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Stock movement history --}}
    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-base font-semibold text-gray-900">Stock Movement History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Quantity</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reason</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">User</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($stockMovements as $movement)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $movement->type === 'in' ? 'bg-emerald-100 text-emerald-800' : ($movement->type === 'sale' || $movement->type === 'out' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700') }}">
                                    {{ ucfirst($movement->type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700">{{ $movement->quantity }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $movement->reason ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500">No stock movements yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
