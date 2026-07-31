<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Batas maksimal berat buah (kg) yang muat di tiap jenis wadah parsel
    // kustom - dipakai untuk memvalidasi pilihan buah di frontend.
    public function up(): void
    {
        Schema::table('hamper_containers', function (Blueprint $table) {
            $table->decimal('max_weight_kg', 5, 2)->nullable()->after('extra_price');
        });
    }

    public function down(): void
    {
        Schema::table('hamper_containers', function (Blueprint $table) {
            $table->dropColumn('max_weight_kg');
        });
    }
};
