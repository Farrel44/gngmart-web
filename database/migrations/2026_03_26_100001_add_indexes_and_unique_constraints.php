<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan index pada kolom yang sering di-query/filter
 * dan unique constraints untuk menjaga integritas data.
 *
 * Index: mempercepat query filter/sort pada admin panel dan storefront.
 * Unique: memastikan 1 cart per user, 1 item per produk per cart, 1 payment per order.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Unique constraints untuk integritas data
        Schema::table('carts', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('order_id');
        });

        // Index pada kolom yang sering di-filter/sort
        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_status');
            $table->index('order_date');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('payment_status');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['order_status']);
            $table->dropIndex(['order_date']);
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'start_date', 'end_date']);
        });
    }
};
