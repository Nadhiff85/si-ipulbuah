<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Link Google Maps spesifik (bukan API key) - untuk tombol "Buka Lokasi Langsung"
    // di Beranda, supaya pelanggan bisa lihat persis lokasi yang sudah dikonfirmasi Admin.
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('google_maps_link')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('google_maps_link');
        });
    }
};
