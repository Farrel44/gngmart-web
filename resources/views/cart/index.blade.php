@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
@php
    // Serialize cart items untuk Alpine.js reactivity.
    // Server merender halaman; Alpine mengelola state interaktif
    // (checkbox, quantity, total, delete) tanpa page reload.
    $itemsData = $cart->items->map(fn($item) => [
        'id' => $item->id,
        'productName' => $item->product->name,
        'productSlug' => $item->product->slug,
        'imageUrl' => $item->product->images->first()
            ? asset('storage/' . $item->product->images->first()->image_url)
            : asset('images/placeholder.png'),
        'unitPrice' => $item->product->getEffectivePrice(),
        'originalPrice' => (float) $item->product->price,
        'hasDiscount' => $item->product->getDiscountPercentage() > 0,
        'quantity' => $item->quantity,
        'maxQty' => $item->product->stock,
    ])->values();

    // Pre-check: hanya item paling baru yang ter-check saat page load.
    // Item lain unchecked supaya user sadar memilih apa yang mau di-checkout.
    $latestItemId = $cart->items->sortByDesc('updated_at')->first()?->id;
@endphp

<div class="max-w-7xl mx-auto px-4 py-8" x-data="cartPage()">

    {{-- Header --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Keranjang Belanja</h1>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4">
            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Empty State — tampil saat cart kosong atau semua item dihapus via AJAX --}}
    <div x-show="items.length === 0" x-cloak
         class="text-center py-20 bg-white rounded-2xl shadow-sm">
        <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700">Keranjang Kosong</h3>
        <p class="text-sm text-gray-500 mt-1">Anda belum menambahkan produk ke keranjang.</p>
        <a href="{{ route('home') }}"
           class="mt-6 inline-block bg-red-600 text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-red-700 transition">
            Belanja Sekarang
        </a>
    </div>

    {{-- Cart Content --}}
    <div x-show="items.length > 0" x-cloak>

        {{-- Top Bar: Select All + Bulk Delete --}}
        <div class="flex items-center justify-between bg-white rounded-2xl shadow-sm px-4 py-3 mb-4">
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox"
                       x-ref="selectAll"
                       :checked="allChecked"
                       @change="toggleAll()"
                       x-effect="if($refs.selectAll) $refs.selectAll.indeterminate = someChecked && !allChecked"
                       class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                <span class="text-sm font-medium text-gray-700">
                    Pilih Semua (<span x-text="items.length"></span>)
                </span>
            </label>

            <div class="flex items-center gap-2">
                {{-- Inline confirmation muncul saat user klik Hapus --}}
                <template x-if="showBulkConfirm">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-600">
                            Hapus <span x-text="checkedCount" class="font-semibold"></span> item?
                        </span>
                        <button @click="executeBulkDelete()"
                                class="px-3 py-1 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition">
                            Ya, Hapus
                        </button>
                        <button @click="showBulkConfirm = false"
                                class="px-3 py-1 border border-gray-300 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-50 transition">
                            Batal
                        </button>
                    </div>
                </template>

                <template x-if="!showBulkConfirm">
                    <button @click="showBulkConfirm = true"
                            :disabled="checkedCount === 0"
                            class="text-sm font-medium text-red-500 hover:text-red-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
                        Hapus
                    </button>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: Cart Items --}}
            <div class="lg:col-span-2 space-y-4">
                <template x-for="item in items" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-sm p-4 flex gap-4 items-start">

                        {{-- Checkbox --}}
                        <input type="checkbox"
                               x-model="item.checked"
                               class="mt-2 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500 flex-shrink-0 cursor-pointer">

                        {{-- Thumbnail --}}
                        <a :href="'/products/' + item.productSlug" class="flex-shrink-0">
                            <img :src="item.imageUrl"
                                 :alt="item.productName"
                                 class="w-20 h-20 rounded-xl object-contain bg-gray-50">
                        </a>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <a :href="'/products/' + item.productSlug"
                               class="text-sm font-semibold text-gray-900 hover:text-red-600 transition line-clamp-2"
                               x-text="item.productName"></a>

                            {{-- Unit Price (harga per-pcs saja, tanpa subtotal) --}}
                            <div class="mt-1">
                                <template x-if="item.hasDiscount">
                                    <div>
                                        <span class="text-xs text-gray-400 line-through"
                                              x-text="formatPrice(item.originalPrice)"></span>
                                        <span class="text-sm font-bold text-red-600 ml-1"
                                              x-text="formatPrice(item.unitPrice)"></span>
                                    </div>
                                </template>
                                <template x-if="!item.hasDiscount">
                                    <span class="text-sm font-bold text-gray-900"
                                          x-text="formatPrice(item.unitPrice)"></span>
                                </template>
                            </div>

                            {{-- Quantity Controls + Delete --}}
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center gap-2">
                                    <button @click="decrementQty(item)"
                                            :disabled="item.quantity <= 1"
                                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-10 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                    <button @click="incrementQty(item)"
                                            :disabled="item.quantity >= item.maxQty"
                                            class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Delete single item --}}
                                <button @click="deleteItem(item)"
                                        class="text-gray-400 hover:text-red-500 transition" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- RIGHT: Ringkasan Pesanan —
                 Hanya menghitung item yang di-check.
                 Update real-time saat checkbox/quantity berubah. --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Ringkasan Pesanan</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Total Item</span>
                            <span class="font-medium text-gray-900">
                                <span x-text="checkedItemQuantity"></span> barang
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Total Harga</span>
                            <span class="font-bold text-gray-900"
                                  x-text="formatPrice(checkedTotal)"></span>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-100">

                    <div class="flex justify-between items-center mb-4">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-lg font-bold text-red-600"
                              x-text="formatPrice(checkedTotal)"></span>
                    </div>

                    <button @click="goToCheckout()"
                            :disabled="checkedCount === 0"
                            class="w-full text-center bg-red-600 text-white font-semibold py-3 rounded-xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Beli (<span x-text="checkedCount"></span>)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function cartPage() {
        const rawItems = @json($itemsData);
        const latestId = @json($latestItemId);

        // Hanya item terakhir yang di-update (baru ditambah/diubah) yang pre-checked.
        // Item lain unchecked supaya user secara sadar memilih apa yang mau checkout.
        rawItems.forEach(item => {
            item.checked = item.id === latestId;
        });

        return {
            items: rawItems,
            showBulkConfirm: false,

            // Timer per-item untuk debounced quantity sync ke server
            _syncTimers: {},

            // --- Computed Properties ---

            get allChecked() {
                return this.items.length > 0 && this.items.every(i => i.checked);
            },

            get someChecked() {
                return this.items.some(i => i.checked);
            },

            get checkedCount() {
                return this.items.filter(i => i.checked).length;
            },

            get checkedItemQuantity() {
                return this.items
                    .filter(i => i.checked)
                    .reduce((sum, i) => sum + i.quantity, 0);
            },

            // Total harga = hanya item yang di-check × quantity masing-masing
            get checkedTotal() {
                return Math.round(
                    this.items
                        .filter(i => i.checked)
                        .reduce((sum, i) => sum + (i.unitPrice * i.quantity), 0)
                );
            },

            // --- Helpers ---

            formatPrice(amount) {
                return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
            },

            get csrfToken() {
                return document.querySelector('meta[name="csrf-token"]').content;
            },

            // --- Actions ---

            toggleAll() {
                const target = !this.allChecked;
                this.items.forEach(i => i.checked = target);
            },

            incrementQty(item) {
                if (item.quantity < item.maxQty) {
                    item.quantity++;
                    this.debouncedSyncQty(item);
                }
            },

            decrementQty(item) {
                if (item.quantity > 1) {
                    item.quantity--;
                    this.debouncedSyncQty(item);
                }
            },

            /**
             * Debounced sync: tunggu 400ms setelah klik terakhir baru kirim ke server.
             * Rapid +/+/+ hanya menghasilkan satu request.
             * Jika server reject (stok habis), quantity di-rollback.
             */
            debouncedSyncQty(item) {
                clearTimeout(this._syncTimers[item.id]);
                this._syncTimers[item.id] = setTimeout(() => this.syncQty(item), 400);
            },

            async syncQty(item) {
                const prevQty = item.quantity;
                try {
                    const res = await fetch(`/cart/${item.id}/quantity`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ quantity: item.quantity }),
                    });

                    if (!res.ok) {
                        const data = await res.json();
                        // Rollback ke max yang server izinkan
                        item.quantity = data.max_quantity ?? prevQty;
                    }
                } catch {
                    item.quantity = prevQty;
                }
            },

            /**
             * Paksa kirim semua pending sync sebelum navigasi.
             * Menjamin DB punya quantity terbaru sebelum checkout.
             */
            async flushPendingSync() {
                const pending = [];
                for (const [itemId, timer] of Object.entries(this._syncTimers)) {
                    clearTimeout(timer);
                    const item = this.items.find(i => i.id == itemId);
                    if (item) pending.push(this.syncQty(item));
                }
                this._syncTimers = {};
                await Promise.all(pending);
            },

            async deleteItem(item) {
                try {
                    const res = await fetch(`/cart/${item.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (res.ok) {
                        this.items = this.items.filter(i => i.id !== item.id);
                        this.updateNavBadge();
                    }
                } catch (e) {
                    console.error('Delete item failed:', e);
                }
            },

            async executeBulkDelete() {
                const ids = this.items.filter(i => i.checked).map(i => i.id);
                if (ids.length === 0) return;

                try {
                    const res = await fetch('/cart/bulk', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids }),
                    });

                    if (res.ok) {
                        this.items = this.items.filter(i => !ids.includes(i.id));
                        this.showBulkConfirm = false;
                        this.updateNavBadge();
                    }
                } catch (e) {
                    console.error('Bulk delete failed:', e);
                }
            },

            /** Sinkronkan badge keranjang di navbar setelah mutasi */
            updateNavBadge() {
                const badge = document.getElementById('cart-count-badge');
                if (!badge) return;

                const total = this.items.reduce((sum, i) => sum + i.quantity, 0);
                if (total > 0) {
                    badge.textContent = total > 99 ? '99+' : total;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            },

            /**
             * Checkout: kirim hanya item yang di-check.
             * Flush pending sync dulu supaya DB punya qty terbaru.
             */
            async goToCheckout() {
                if (this.checkedCount === 0) return;

                await this.flushPendingSync();

                const ids = this.items.filter(i => i.checked).map(i => i.id).join(',');
                window.location.href = `{{ route('checkout.index') }}?items=${ids}`;
            },
        };
    }
</script>
@endsection
