<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\CarouselSlide;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database dengan data realistis untuk toko GnG Mart.
     *
     * Data mencakup: admin, user, kategori, produk (dengan gambar),
     * carousel slides, dan promosi aktif.
     */
    public function run(): void
    {
        // ─── Admin & User ─────────────────────────────────────────
        Admin::factory()->create([
            'name' => 'Admin GNGMart',
            'email' => 'admin@gngmart.com',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // ─── Kategori ────────────────────────────────────────────
        $categories = collect([
            'Buah & Sayur',
            'Daging & Seafood',
            'Minuman',
            'Makanan Ringan',
            'Roti & Bakery',
            'Kebutuhan Dapur',
        ])->mapWithKeys(function ($name) {
            $cat = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);

            return [$name => $cat];
        });

        // ─── Produk per Kategori ──────────────────────────────────
        // Format: [nama, harga, discount_price, stok, berat, gambar]
        $productsData = [
            'Buah & Sayur' => [
                ['Apel Fuji Premium', 45000, null, 50, '1 kg', 'apel.png'],
                ['Tomat Segar Organik', 15000, 12000, 80, '500 g', 'tomat.png'],
            ],
            'Daging & Seafood' => [
                ['Daging Ayam Fillet', 35000, null, 30, '500 g', 'ayam.png'],
                ['Salmon Fillet Segar', 85000, 72000, 15, '300 g', 'salmon.png'],
            ],
            'Minuman' => [
                ['Susu Segar Ultra', 18500, null, 100, '1 Liter', 'susu.png'],
                ['Jus Jeruk Segar', 25000, null, 60, '250 ml', 'jusjeruk.png'],
                ['Teh Hijau Botol', 8500, null, 120, '500 ml', 'tehhijau.png'],
                ['Yogurt Greek Plain', 32000, 28000, 40, '450 g', 'yogurt.png'],
            ],
            'Makanan Ringan' => [
                ['Keripik Kentang BBQ', 16000, 13500, 90, '150 g', 'chips.png'],
                ['Cookies Cokelat Chip', 28500, 24000, 45, '300 g', 'cookies.png'],
            ],
            'Roti & Bakery' => [
                ['Roti Gandum Sehat', 22000, null, 55, '500 g', 'roti.png'],
            ],
            'Kebutuhan Dapur' => [
                ['Mi Instan Goreng', 12500, null, 200, '5 pcs', 'mie.png'],
            ],
        ];

        foreach ($productsData as $categoryName => $items) {
            $category = $categories[$categoryName];

            foreach ($items as [$name, $price, $discountPrice, $stock, $weight, $image]) {
                $product = Product::create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => "Produk berkualitas {$name} dari GnG Mart.",
                    'price' => $price,
                    'discount_price' => $discountPrice,
                    'stock' => $stock,
                    'weight' => $weight,
                ]);

                // Setiap produk mendapat 1 gambar dari folder public/images/products
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => 'images/products/'.$image,
                ]);
            }
        }

        // ─── Carousel Slides ──────────────────────────────────────
        // Menggunakan logo sebagai placeholder gambar
        // Admin bisa mengganti via Filament panel nanti
        $slides = [
            [
                'title' => 'Paket Keluarga Hemat Minggu Ini!',
                'subtitle' => 'Promo Mingguan',
                'description' => 'Belanja lebih banyak, hemat lebih banyak! Diskon hingga 30% untuk kebutuhan keluarga.',
                'button_text' => 'Lihat Promo',
                'button_url' => '/products',
                'order_column' => 1,
            ],
            [
                'title' => 'Diskon Spesial Buah Segar!',
                'subtitle' => 'Fresh Deals',
                'description' => 'Dapatkan potongan hingga 40% untuk semua buah pilihan minggu ini.',
                'button_text' => 'Belanja Sekarang',
                'button_url' => '/products',
                'order_column' => 2,
            ],
            [
                'title' => 'Promo Member Eksklusif!',
                'subtitle' => 'Member Only',
                'description' => 'Nikmati keuntungan menjadi member setia kami dengan cashback menarik.',
                'button_text' => 'Daftar Sekarang',
                'button_url' => '/register',
                'order_column' => 3,
            ],
        ];

        foreach ($slides as $slideData) {
            // Copy logo ke storage/app/public/carousel agar bisa diakses via asset('storage/...')
            $carouselDir = storage_path('app/public/carousel');
            if (! is_dir($carouselDir)) {
                mkdir($carouselDir, 0755, true);
            }

            $filename = 'slide-'.$slideData['order_column'].'.png';
            $destination = $carouselDir.'/'.$filename;
            $source = public_path('images/logo.png');

            if (file_exists($source) && ! file_exists($destination)) {
                copy($source, $destination);
            }

            CarouselSlide::create(array_merge($slideData, [
                'image_path' => 'carousel/'.$filename,
                'is_active' => true,
            ]));
        }

        // ─── Promosi ─────────────────────────────────────────────
        // Promo 1: Ramadhan Sale — berlaku untuk kategori Buah & Sayur
        $ramadhanSale = Promotion::create([
            'name' => 'Ramadhan Sale',
            'discount_percentage' => 20,
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(28),
            'is_active' => true,
        ]);
        $ramadhanSale->categories()->attach($categories['Buah & Sayur']->id);

        // Promo 2: Flash Sale Minuman — berlaku untuk kategori Minuman
        $flashMinuman = Promotion::create([
            'name' => 'Flash Sale Minuman',
            'discount_percentage' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
            'is_active' => true,
        ]);
        $flashMinuman->categories()->attach($categories['Minuman']->id);

        // Promo 3: Snack Mania — berlaku untuk produk snack tertentu
        $snackMania = Promotion::create([
            'name' => 'Snack Mania',
            'discount_percentage' => 25,
            'start_date' => now(),
            'end_date' => now()->addDays(14),
            'is_active' => true,
        ]);
        $snackProducts = Product::whereIn('slug', ['keripik-kentang-bbq', 'cookies-cokelat-chip'])->pluck('id');
        $snackMania->products()->attach($snackProducts);

        // Promo 4: Promo sudah berakhir (untuk testing filter)
        Promotion::create([
            'name' => 'Promo Tahun Baru',
            'discount_percentage' => 30,
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(30),
            'is_active' => false,
        ]);
    }
}
