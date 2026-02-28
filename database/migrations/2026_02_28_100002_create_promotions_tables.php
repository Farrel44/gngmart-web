<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: promotions + pivot tables
 *
 * Sistem promo fleksibel yang bisa diterapkan ke produk atau kategori tertentu.
 * Menggunakan pivot tables untuk relasi many-to-many.
 * Tidak mengubah tabel products yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tabel utama promosi
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('discount_percentage');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot: promosi berlaku untuk produk tertentu
        Schema::create('promotion_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['promotion_id', 'product_id']);
        });

        // Pivot: promosi berlaku untuk seluruh kategori
        Schema::create('promotion_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['promotion_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_category');
        Schema::dropIfExists('promotion_product');
        Schema::dropIfExists('promotions');
    }
};
