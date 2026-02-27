<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom payment_proof untuk menyimpan path file bukti pembayaran.
     * Kolom ini nullable karena COD tidak memerlukan bukti bayar.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Path file bukti bayar (untuk transfer/e-wallet)
            // Nullable karena COD tidak perlu upload bukti
            $table->string('payment_proof')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_proof');
        });
    }
};
