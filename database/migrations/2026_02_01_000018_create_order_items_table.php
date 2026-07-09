<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained();
            $table->foreignId('hamper_id')->nullable()->constrained();
            $table->foreignId('product_variant_id')->nullable()->constrained();
            $table->string('item_name'); // snapshot nama saat transaksi
            $table->integer('qty');
            $table->decimal('price', 12, 2); // harga per item saat transaksi
            $table->decimal('cost_price', 12, 2)->nullable(); // snapshot harga modal (utk laporan margin)
            $table->text('note')->nullable();
            $table->json('custom_hamper_config')->nullable();
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
