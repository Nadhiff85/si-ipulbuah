<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // INV/20260704/0001
            $table->foreignId('user_id')->constrained();
            $table->foreignId('outlet_id')->nullable()->constrained();
            $table->enum('fulfillment_type', ['delivery', 'pickup']);
            $table->foreignId('address_id')->nullable()->constrained();
            $table->foreignId('delivery_region_id')->nullable()->constrained();
            $table->foreignId('delivery_slot_id')->nullable()->constrained();
            $table->date('scheduled_date'); // tanggal kirim/ambil
            $table->text('note')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', [
                'menunggu_bayar', 'dikonfirmasi', 'diproses', 'dikirim_siap_ambil', 'selesai', 'dibatalkan'
            ])->default('menunggu_bayar');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
