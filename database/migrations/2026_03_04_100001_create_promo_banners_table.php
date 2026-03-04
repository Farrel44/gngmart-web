<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: promo_banners
 *
 * Tabel untuk promo banner section (bagian bawah di homepage).
 * Berbeda dengan carousel_slides, ini hanya menampilkan 1 banner dengan desain khusus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('background_color')->default('from-green-300 via-green-400 to-green-500');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_banners');
    }
};
