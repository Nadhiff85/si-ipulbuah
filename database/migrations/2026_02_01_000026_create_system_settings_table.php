<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('session_timeout_minutes')->default(60);
            $table->integer('max_login_attempts')->default(5);
            $table->integer('rate_limit_per_minute')->default(5);
            $table->boolean('maintenance_mode')->default(false);
            $table->integer('max_freshness_days_default')->default(7); // batas masa aktif produk
            $table->boolean('delivery_globally_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
