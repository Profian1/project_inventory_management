@php($product = $product ?? null)

@csrf

@if ($product)
    @method('PUT')
@endif

<div
    x-data="{
        buying: {{ (float) old('buying_price', $product?->buying_price ?? 0) }},
        selling: {{ (float) old('selling_price', $product?->selling_price ?? 0) }},
        get marginAmount() { return ((Number(this.selling) - Number(this.buying)) || 0).toFixed(2); },
        get marginPercent() { const s = Number(this.selling); return s > 0 ? (((s - Number(this.buying)) / s) * 100).toFixed(2) : '0.00'; },
        generateBarcode() { this.$refs.barcode.value = String(Math.floor(1000000000000 + Math.random() * 9000000000000)); },
    }"
    class="grid grid-cols-1 gap-5 sm:grid-cols-2"
>
    {{-- SKU --}}
    <div>
        <x-input-label for="sku" value="SKU" />
        <x-text-input id="sku" name="sku" class="mt-1 block w-full" :value="old('sku', $product?->sku ?? '')" placeholder="Auto: PRD-0001" />
        <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate.</p>
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    {{-- Name --}}
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $product?->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    {{-- Description --}}
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $product?->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    {{-- Category --}}
    <div>
        <x-input-label for="category_id" value="Category" />
        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">— No category —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>

    {{-- Supplier --}}
    <div>
        <x-input-label for="supplier_id" value="Supplier" />
        <select id="supplier_id" name="supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">— No supplier —</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product?->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
    </div>

    {{-- Buying price --}}
    <div>
        <x-input-label for="buying_price" value="Buying Price (KSh)" />
        <x-text-input id="buying_price" name="buying_price" type="number" step="0.01" min="0" class="mt-1 block w-full" x-model.number="buying" required />
        <x-input-error :messages="$errors->get('buying_price')" class="mt-2" />
    </div>

    {{-- Selling price --}}
    <div>
        <x-input-label for="selling_price" value="Selling Price (KSh)" />
        <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="mt-1 block w-full" x-model.number="selling" required />
        <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
    </div>

    {{-- Stock quantity (create only) --}}
    @if ($product === null)
        <div>
            <x-input-label for="stock_quantity" value="Opening Stock" />
            <x-text-input id="stock_quantity" name="stock_quantity" type="number" min="0" class="mt-1 block w-full" :value="old('stock_quantity', 0)" required />
            <x-input-error :messages="$errors->get('stock_quantity')" class="mt-2" />
        </div>
    @endif

    {{-- Reorder level --}}
    <div>
        <x-input-label for="reorder_level" value="Reorder Level" />
        <x-text-input id="reorder_level" name="reorder_level" type="number" min="0" class="mt-1 block w-full" :value="old('reorder_level', $product?->reorder_level ?? 5)" required />
        <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
    </div>

    {{-- Barcode --}}
    <div>
        <x-input-label for="barcode" value="Barcode" />
        <div class="flex gap-2">
            <x-text-input id="barcode" name="barcode" class="mt-1 block w-full" :value="old('barcode', $product?->barcode ?? '')" x-ref="barcode" />
            <button type="button" @click="generateBarcode()" class="mt-1 shrink-0 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Generate</button>
        </div>
        <x-input-error :messages="$errors->get('barcode')" class="mt-2" />
    </div>

    {{-- Image --}}
    <div>
        <x-input-label for="image" value="Product Image" />
        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100" />
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>

    {{-- Active toggle --}}
    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_active', $product?->is_active ?? true)) />
            <span class="text-sm text-gray-700">Active (visible in POS and sales)</span>
        </label>
    </div>

    {{-- Live margin preview --}}
    <div class="rounded-md bg-gray-50 p-4 sm:col-span-2">
        <p class="text-sm">
            <span class="font-medium text-gray-500">Margin:</span>
            <span class="font-semibold text-gray-900" x-text="'KSh ' + marginAmount + ' (' + marginPercent + '%)'"></span>
        </p>
        <p x-show="Number(selling) < Number(buying)" x-cloak class="mt-1 text-sm font-medium text-amber-600">
            Warning: selling price is below buying price — you will make a loss.
        </p>
    </div>
</div>
