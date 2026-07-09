<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Single-row config table (identitas toko)
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('IPUL BUAH');
            $table->string('tagline')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->json('bank_accounts')->nullable(); // [{bank, no_rek, atas_nama}]
            $table->string('qris_image')->nullable();
            $table->json('operating_hours')->nullable(); // {senin: [08:00,17:00], ...}
            $table->decimal('min_order_delivery', 12, 2)->default(50000);
            $table->string('smtp_config')->nullable(); // json encoded/atau referensi .env
            $table->string('whatsapp_api_key')->nullable(); // WaBlas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
