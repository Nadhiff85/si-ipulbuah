<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Riwayat perubahan stok: siapa, kapan, alasan (fitur B.14)
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained(); // admin yang mengubah (null jika sistem otomatis)
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->integer('change'); // selisih (+/-)
            $table->string('reason'); // restock, penjualan, koreksi, produk_kadaluarsa, dll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
