@props(['name' => 'product_id', 'label' => 'Product'])

<div
    x-data="{
        open: false,
        query: '',
        results: [],
        selectedId: {{ old($name) ?? 'null' }},
        selectedName: '',
        search() {
            if (this.query.trim().length < 2) { this.results = []; return; }
            fetch('/api/products/search?q=' + encodeURIComponent(this.query.trim()))
                .then(r => r.json())
                .then(data => { this.results = data; this.open = true; });
        },
        select(product) {
            this.selectedId = product.id;
            this.selectedName = product.name + ' (' + product.sku + ')';
            this.query = '';
            this.results = [];
            this.open = false;
        },
        clear() {
            this.selectedId = null;
            this.selectedName = '';
        },
    }"
    class="relative"
>
    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" :value="selectedId" />

    <div class="relative mt-1">
        <input
            type="text"
            x-model="query"
            @input.debounce.300ms="search()"
            @focus="open = true"
            placeholder="Type at least 2 characters to search…"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
        />
        <div
            x-show="open && results.length > 0"
            @click.away="open = false"
            x-cloak
            class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg"
        >
            <template x-for="p in results" :key="p.id">
                <button
                    type="button"
                    @click="select(p)"
                    class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-emerald-50"
                >
                    <span x-text="p.name"></span>
                    <span class="text-xs text-gray-400" x-text="p.sku + ' · ' + p.stock_quantity + ' in stock'"></span>
                </button>
            </template>
        </div>
    </div>

    <div x-show="selectedId" x-cloak class="mt-2 flex items-center gap-2 text-sm text-emerald-700">
        <span>Selected: <span x-text="selectedName"></span></span>
        <button type="button" @click="clear()" class="text-xs font-medium text-red-500 hover:text-red-700">Clear</button>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
