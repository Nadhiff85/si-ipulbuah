<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Menghitung kuota slot pengiriman terpakai per tanggal
    public function up(): void
    {
        Schema::create('delivery_slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_slot_id')->constrained();
            $table->date('date');
            $table->timestamps();

            $table->index(['delivery_slot_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_slot_bookings');
    }
};
