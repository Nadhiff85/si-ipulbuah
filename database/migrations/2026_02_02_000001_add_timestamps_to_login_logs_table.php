<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Menambahkan created_at/updated_at yang tertinggal di migration awal login_logs.
    // Dibutuhkan untuk mengecek waktu percobaan login gagal (fitur C.13 - Max Login Attempts).
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
