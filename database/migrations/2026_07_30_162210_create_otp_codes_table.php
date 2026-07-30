<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kode OTP (6 digit) dikirim lewat email untuk verifikasi login (2FA) &
    // reset kata sandi lupa. Kode disimpan sebagai hash (bukan plain text) -
    // sama seperti kolom password - supaya tidak terbaca kalau tabel bocor.
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('challenge', 64)->unique(); // dikirim ke frontend, dipakai utk verify/resend tanpa expose email/user_id
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('purpose', ['login', 'reset_password']);
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('last_sent_at')->nullable(); // dipakai utk jeda minimum antar kirim ulang
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
