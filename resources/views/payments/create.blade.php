<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Ringkasan Pesanan --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Ringkasan Pesanan</h3>
                        
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            <p><strong>Order #{{ $order->id }}</strong></p>
                            <p>{{ $order->order_date->format('d M Y, H:i') }}</p>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td class="py-2">
                                                {{ $item->product->name ?? 'Produk Dihapus' }}
                                                <span class="text-gray-500 text-xs">(x{{ $item->quantity }})</span>
                                            </td>
                                            <td class="py-2 text-right">
                                                Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total</span>
                                <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="font-medium mb-2">Alamat Pengiriman</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $order->address_shipment }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Form Pembayaran --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Pilih Metode Pembayaran</h3>
                        
                        <form method="POST" action="{{ route('payment.store', $order) }}" enctype="multipart/form-data" id="payment-form">
                            @csrf

                            {{-- Payment Method Selection --}}
                            <div class="space-y-3 mb-6">
                                @foreach ($paymentMethods as $method => $label)
                                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ old('payment_method') === $method ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                        <input type="radio" 
                                               name="payment_method" 
                                               value="{{ $method }}"
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                                               {{ old('payment_method') === $method ? 'checked' : '' }}
                                               onchange="toggleProofUpload(this.value)">
                                        <div class="ml-3">
                                            <span class="font-medium">{{ $label }}</span>
                                            @if ($method === 'cod')
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Bayar tunai saat pesanan diantar</p>
                                            @elseif ($method === 'transfer')
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Transfer ke rekening toko, upload bukti bayar</p>
                                            @elseif ($method === 'ewallet')
                                                <p class="text-xs text-gray-500 dark:text-gray-400">OVO, GoPay, DANA, dll - upload screenshot</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Upload Bukti Bayar (hidden by default, shown for transfer/ewallet) --}}
                            <div id="proof-upload-section" class="hidden mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Unggah Bukti Pembayaran <span class="text-red-500">*</span>
                                </label>
                                
                                {{-- Info Bank --}}
                                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm">
                                    <p class="font-medium text-blue-800 dark:text-blue-300 mb-2">Informasi Transfer:</p>
                                    <p class="text-blue-700 dark:text-blue-400">Bank BCA: 1234567890</p>
                                    <p class="text-blue-700 dark:text-blue-400">a.n. GNG Mart</p>
                                </div>

                                <input type="file" 
                                       name="payment_proof" 
                                       id="payment_proof"
                                       accept="image/jpeg,image/png"
                                       class="block w-full text-sm text-gray-500 dark:text-gray-400
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-indigo-50 file:text-indigo-700
                                              dark:file:bg-indigo-900 dark:file:text-indigo-200
                                              hover:file:bg-indigo-100
                                              dark:hover:file:bg-indigo-800">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Format: JPG, PNG. Maksimal 2MB.
                                </p>
                            </div>

                            {{-- COD Info --}}
                            <div id="cod-info-section" class="hidden mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                    <strong>Catatan COD:</strong> Siapkan uang pas sebesar 
                                    <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong> 
                                    saat pesanan diantar.
                                </p>
                            </div>

                            {{-- Submit Button --}}
                            <button type="submit" 
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    id="submit-btn"
                                    disabled>
                                Konfirmasi Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Back Link --}}
            <div class="mt-6 text-center">
                <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    &larr; Kembali ke Daftar Pesanan
                </a>
            </div>
        </div>
    </div>

    {{-- JavaScript for toggling proof upload section --}}
    <script>
        function toggleProofUpload(method) {
            const proofSection = document.getElementById('proof-upload-section');
            const codSection = document.getElementById('cod-info-section');
            const proofInput = document.getElementById('payment_proof');
            const submitBtn = document.getElementById('submit-btn');
            
            if (method === 'cod') {
                proofSection.classList.add('hidden');
                codSection.classList.remove('hidden');
                proofInput.removeAttribute('required');
            } else {
                proofSection.classList.remove('hidden');
                codSection.classList.add('hidden');
                proofInput.setAttribute('required', 'required');
            }
            
            // Enable submit button
            submitBtn.disabled = false;
        }

        // Check on page load if there's an old value
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
            if (checkedRadio) {
                toggleProofUpload(checkedRadio.value);
            }
        });
    </script>
</x-app-layout>
