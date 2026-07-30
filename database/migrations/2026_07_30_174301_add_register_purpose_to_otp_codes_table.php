<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Tambahkan 'register' ke daftar purpose OTP (sebelumnya cuma login &
    // reset_password) - dipakai untuk verifikasi email saat daftar akun baru.
    // Pakai raw SQL, bukan $table->enum()->change(), supaya tidak butuh
    // paket doctrine/dbal tambahan hanya demi mengubah satu kolom enum.
    public function up(): void
    {
        DB::statement("ALTER TABLE otp_codes MODIFY purpose ENUM('login', 'reset_password', 'register') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE otp_codes MODIFY purpose ENUM('login', 'reset_password') NOT NULL");
    }
};
