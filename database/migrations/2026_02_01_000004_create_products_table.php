<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('origin_region')->nullable(); // asal daerah (mis. Palu, luar negeri)
            $table->enum('origin_type', ['lokal', 'impor'])->default('lokal');
            $table->string('unit')->default('kg'); // kg | pcs
            $table->decimal('price_unit', 12, 2); // harga satuan
            $table->decimal('price_wholesale', 12, 2)->nullable(); // harga grosir
            $table->integer('wholesale_min_qty')->nullable(); // minimum qty utk harga grosir
            $table->decimal('cost_price', 12, 2)->nullable(); // harga modal (utk estimasi margin - superadmin/admin)
            $table->integer('stock')->default(0);
            $table->integer('min_stock_alert')->default(5); // batas stok hampir habis
            $table->integer('freshness_days')->nullable(); // estimasi kesegaran (hari)
            $table->date('stock_in_date')->nullable(); // tanggal masuk (utk hitung masa aktif produk)
            $table->text('storage_tips')->nullable(); // tips penyimpanan
            $table->json('labels')->nullable(); // ["best_seller","promo","musiman","segar"]
            $table->boolean('is_seasonal')->default(false);
            $table->boolean('is_active')->default(true); // otomatis nonaktif jika lewat masa kesegaran
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('sold_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
