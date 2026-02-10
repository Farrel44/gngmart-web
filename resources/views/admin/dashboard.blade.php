<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">{{ __('Selamat datang, ') }} {{ Auth::guard('admin')->user()->name }}!</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <!-- Card: Manage Orders -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-blue-700 dark:text-blue-300">{{ __('Manage Orders') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ __('Kelola pesanan pelanggan') }}</p>
                        </div>

                        <!-- Card: Manage Categories -->
                        <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-green-700 dark:text-green-300">{{ __('Manage Categories') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ __('Kelola kategori produk') }}</p>
                        </div>

                        <!-- Card: Manage Products -->
                        <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg">
                            <h4 class="font-semibold text-purple-700 dark:text-purple-300">{{ __('Manage Products') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ __('Kelola produk dan stok') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
