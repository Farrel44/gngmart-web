<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database dengan data awal yang dibutuhkan untuk development
     */
    public function run(): void
    {
        // Buat default admin untuk akses panel Filament di /admin
        Admin::factory()->create([
            'name' => 'Admin GNGMart',
            'email' => 'admin@gngmart.com',
        ]);

        // Buat default user untuk testing login di sisi customer
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Buat beberapa kategori dan produk sebagai data sample
        $categories = Category::factory(5)->create();

        $categories->each(function (Category $category) {
            Product::factory(3)->create([
                'category_id' => $category->id,
            ]);
        });
    }
}
