<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Sejak registrasi mewajibkan verifikasi OTP email, akun yang dibuat
    // SEBELUM fitur ini ada (termasuk admin/superadmin seed & pelanggan yang
    // sudah lebih dulu daftar) masih punya email_verified_at NULL. Tanpa
    // backfill ini, login() akan menganggap semua akun lama itu "belum
    // terverifikasi" dan mengunci mereka keluar - padahal mereka sudah
    // dipakai normal sebelum fitur ini dipasang.
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // Sengaja tidak dikembalikan ke NULL - tidak ada cara membedakan
        // baris yang backfill ini set vs yang memang sudah terverifikasi
        // secara normal sebelum rollback dijalankan.
    }
};
