<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Pilihan kartu ucapan untuk parsel kustom
    public function up(): void
    {
        Schema::create('hamper_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: Kartu Ucapan Selamat, Kartu Duka Cita
            $table->string('image')->nullable();
            $table->decimal('extra_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hamper_cards');
    }
};
