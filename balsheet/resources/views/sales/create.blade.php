<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Point of Sale</h2>
            <a href="{{ route('sales.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Sales History</a>
        </div>
    </x-slot>

    <div x-data="pos()" @keydown.f2.window.prevent="focusSearch()" @keydown.f9.window.prevent="completeSale()">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- LEFT: product grid --}}
            <div class="space-y-4 lg:col-span-2">
                <input
                    x-ref="searchInput"
                    x-model="searchQuery"
                    @keydown.enter.prevent="addFirstResult()"
                    type="text"
                    placeholder="Search products… (F2 to focus, Enter adds first result)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                />

                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="filterCategory = null" :class="filterCategory === null ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="rounded-full px-3 py-1 text-sm font-medium">All</button>
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button" @click="filterCategory = cat.id" :class="filterCategory === cat.id ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="rounded-full border px-3 py-1 text-sm font-medium" x-text="cat.name"></button>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button
                            type="button"
                            @click="addToCart(product)"
                            :disabled="product.stock_quantity <= 0"
                            class="flex flex-col items-start gap-1 rounded-lg border border-gray-200 bg-white p-3 text-left shadow-sm transition hover:border-emerald-400 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span class="text-sm font-medium text-gray-900" x-text="product.name"></span>
                            <span class="text-sm font-semibold text-emerald-700" x-text="money(product.selling_price)"></span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="product.stock_quantity > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'" x-text="product.stock_quantity > 0 ? 'In · ' + product.stock_quantity : 'Out'"></span>
                        </button>
                    </template>
                </div>

                <p x-show="filteredProducts.length === 0" class="py-8 text-center text-sm text-gray-500">No products match your search.</p>
            </div>

            {{-- RIGHT: cart --}}
            <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Cart</h3>

                <div class="mt-3 space-y-3">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="item.name"></p>
                                <div class="mt-1 flex items-center gap-2">
                                    <button type="button" @click="decrement(index)" class="flex h-6 w-6 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-50">−</button>
                                    <span class="w-6 text-center text-sm" x-text="item.quantity"></span>
                                    <button type="button" @click="increment(index)" class="flex h-6 w-6 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-50">+</button>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900" x-text="money(item.subtotal)"></p>
                                <button type="button" @click="remove(index)" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                            </div>
                        </div>
                    </template>
                </div>

                <p x-show="cart.length === 0" class="py-6 text-center text-sm text-gray-500">Cart is empty — click a product to add it.</p>

                {{-- Customer --}}
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <label class="block text-sm font-medium text-gray-700">Customer</label>
                    <input
                        type="text"
                        x-model="customerQuery"
                        @input.debounce.300ms="searchCustomers()"
                        @focus="customerOpen = true"
                        placeholder="Walk-in Customer (type to search)"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    />
                    <div x-show="customerOpen && customerResults.length > 0" @click.away="customerOpen = false" x-cloak class="absolute z-20 mt-1 w-72 rounded-md border border-gray-200 bg-white shadow-lg">
                        <template x-for="c in customerResults" :key="c.id">
                            <button type="button" @click="selectCustomer(c)" class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-emerald-50">
                                <span x-text="c.name"></span>
                                <span class="text-xs text-gray-400" x-text="c.phone"></span>
                            </button>
                        </template>
                    </div>
                    <p x-show="customerId" x-cloak class="mt-1 text-sm text-emerald-700">
                        Customer: <span x-text="customerName"></span>
                        <button type="button" @click="clearCustomer()" class="ml-2 text-xs font-medium text-red-500 hover:text-red-700">Clear (Walk-in)</button>
                    </p>
                </div>

                {{-- Discount --}}
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <label class="block text-sm font-medium text-gray-700">Discount</label>
                    <div class="mt-1 flex gap-2">
                        <input type="number" x-model.number="discountValue" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <select x-model="discountMode" class="rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="ksh">KSh</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>

                {{-- VAT toggle --}}
                <label class="mt-4 inline-flex items-center gap-2">
                    <input type="checkbox" x-model="applyVat" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                    <span class="text-sm text-gray-700">Apply VAT ({{ $vatRate }}%)</span>
                </label>

                {{-- Payment method --}}
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <span class="block text-sm font-medium text-gray-700">Payment Method</span>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <button type="button" @click="paymentMethod = 'cash'" :class="paymentMethod === 'cash' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="rounded-md border px-3 py-2 text-sm font-medium">Cash</button>
                        <button type="button" @click="paymentMethod = 'mpesa'" :class="paymentMethod === 'mpesa' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="rounded-md border px-3 py-2 text-sm font-medium">M-Pesa</button>
                        <button type="button" @click="paymentMethod = 'credit'" :class="paymentMethod === 'credit' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300'" class="rounded-md border px-3 py-2 text-sm font-medium">Credit</button>
                    </div>
                </div>

                {{-- Cash amount + change --}}
                <div x-show="paymentMethod === 'cash'" x-cloak class="mt-4 border-t border-gray-200 pt-4">
                    <label class="block text-sm font-medium text-gray-700">Amount Paid</label>
                    <input type="number" x-model.number="amountPaid" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    <p x-show="change >= 0" class="mt-1 text-sm text-emerald-700">Change: <span x-text="money(change)"></span></p>
                    <p x-show="change < 0" x-cloak class="mt-1 text-sm font-medium text-red-600">Short by <span x-text="money(-change)"></span></p>
                </div>

                <p x-show="paymentMethod === 'credit' && !customerId" x-cloak class="mt-3 text-sm font-medium text-amber-600">Credit sales require a customer.</p>

                {{-- Totals --}}
                <div class="mt-4 space-y-1 border-t border-gray-200 pt-4 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span x-text="money(subtotal)"></span></div>
                    <div x-show="discountAmount > 0" x-cloak class="flex justify-between"><span class="text-gray-500">Discount</span><span x-text="'- ' + money(discountAmount)"></span></div>
                    <div x-show="vatAmount > 0" x-cloak class="flex justify-between"><span class="text-gray-500">VAT</span><span x-text="money(vatAmount)"></span></div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 text-2xl font-bold text-gray-900"><span>Total</span><span x-text="money(total)"></span></div>
                </div>

                <button type="button" @click="completeSale()" :disabled="cart.length === 0" class="mt-4 w-full rounded-md bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Complete Sale (F9)
                </button>
            </div>
        </div>

        {{-- Hidden form submitted by "Complete Sale" --}}
        <form method="POST" action="{{ route('sales.store') }}" x-ref="saleForm" class="hidden">
            @csrf
            <input type="hidden" name="items" :value="JSON.stringify(cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })))" />
            <input type="hidden" name="customer_id" :value="customerId" />
            <input type="hidden" name="discount" :value="discountAmount" />
            <input type="hidden" name="apply_vat" :value="applyVat ? 1 : 0" />
            <input type="hidden" name="payment_method" :value="paymentMethod" />
            <input type="hidden" name="amount_paid" :value="amountPaid" />
        </form>
    </div>

    <script>
        function pos() {
            return {
                products: @json($products),
                categories: @json($categories),
                vatRate: {{ $vatRate }},

                searchQuery: '',
                filterCategory: null,
                cart: [],

                customerId: null,
                customerName: '',
                customerQuery: '',
                customerResults: [],
                customerOpen: false,

                discountValue: 0,
                discountMode: 'ksh',
                applyVat: false,
                paymentMethod: 'cash',
                amountPaid: 0,

                get filteredProducts() {
                    let list = this.products;
                    if (this.filterCategory) list = list.filter(p => p.category_id === this.filterCategory);
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        list = list.filter(p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q));
                    }
                    return list;
                },
                get subtotal() {
                    return this.cart.reduce((sum, i) => sum + i.quantity * i.unit_price, 0);
                },
                get discountAmount() {
                    return this.discountMode === 'percent' ? this.subtotal * this.discountValue / 100 : this.discountValue;
                },
                get vatAmount() {
                    return this.applyVat ? (this.subtotal - this.discountAmount) * this.vatRate / 100 : 0;
                },
                get total() {
                    return this.subtotal - this.discountAmount + this.vatAmount;
                },
                get change() {
                    return this.paymentMethod === 'cash' ? this.amountPaid - this.total : 0;
                },

                money(n) {
                    return 'KSh ' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                addToCart(product) {
                    const existing = this.cart.find(i => i.product_id === product.id);
                    if (existing) {
                        if (existing.quantity >= product.stock_quantity) return;
                        existing.quantity++;
                        existing.subtotal = existing.quantity * existing.unit_price;
                    } else {
                        if (product.stock_quantity <= 0) return;
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            unit_price: Number(product.selling_price),
                            stock_quantity: product.stock_quantity,
                            quantity: 1,
                            subtotal: Number(product.selling_price),
                        });
                    }
                },
                increment(index) {
                    const item = this.cart[index];
                    if (item.quantity >= item.stock_quantity) return;
                    item.quantity++;
                    item.subtotal = item.quantity * item.unit_price;
                },
                decrement(index) {
                    const item = this.cart[index];
                    item.quantity--;
                    if (item.quantity <= 0) this.cart.splice(index, 1);
                    else item.subtotal = item.quantity * item.unit_price;
                },
                remove(index) {
                    this.cart.splice(index, 1);
                },

                searchCustomers() {
                    if (this.customerQuery.trim().length < 2) { this.customerResults = []; return; }
                    fetch('/api/customers/search?q=' + encodeURIComponent(this.customerQuery.trim()))
                        .then(r => r.json())
                        .then(data => { this.customerResults = data; this.customerOpen = true; });
                },
                selectCustomer(c) {
                    this.customerId = c.id;
                    this.customerName = c.name;
                    this.customerQuery = '';
                    this.customerResults = [];
                    this.customerOpen = false;
                },
                clearCustomer() {
                    this.customerId = null;
                    this.customerName = '';
                },

                focusSearch() {
                    this.$refs.searchInput.focus();
                },
                addFirstResult() {
                    if (this.filteredProducts.length > 0) this.addToCart(this.filteredProducts[0]);
                },
                completeSale() {
                    if (this.cart.length === 0) return;
                    if (this.paymentMethod === 'credit' && !this.customerId) {
                        alert('Credit sales require a customer.');
                        return;
                    }
                    if (this.paymentMethod === 'cash' && this.amountPaid < this.total) {
                        alert('Amount paid is less than the total.');
                        return;
                    }
                    this.$refs.saleForm.submit();
                },
            };
        }
    </script>
</x-app-layout>
