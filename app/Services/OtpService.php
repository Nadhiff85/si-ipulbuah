<?php

namespace App\Services;

use App\Exceptions\OtpException;
use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Menangani seluruh siklus kode OTP (One-Time Password) 6 digit yang
 * dikirim lewat email - dipakai untuk verifikasi login (2FA) dan untuk
 * reset kata sandi lupa. Kode TIDAK PERNAH disimpan mentah di database,
 * hanya hash-nya (sama seperti kolom password) supaya tetap aman kalau
 * tabel otp_codes bocor.
 */
class OtpService
{
    public const CODE_LENGTH = 6;
    public const EXPIRES_MINUTES = 5;
    public const MAX_ATTEMPTS = 5;
    public const RESEND_COOLDOWN_SECONDS = 45;

    /**
     * Terbitkan kode OTP baru untuk user & kirim ke emailnya.
     * Baris OTP dihapus lagi kalau pengiriman email gagal, supaya tidak
     * ada kode "hantu" di database yang tidak pernah sampai ke user dan
     * membuatnya terjebak tanpa cara masuk.
     */
    public function issue(User $user, string $purpose, ?string $ip = null): OtpCode
    {
        $code = $this->generateCode();

        $otp = OtpCode::create([
            'challenge' => Str::random(48),
            'user_id' => $user->id,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            'last_sent_at' => now(),
            'ip_address' => $ip,
        ]);

        $this->sendMail($user, $code, $purpose, $otp);

        return $otp;
    }

    /**
     * Kirim ulang kode untuk sebuah challenge, dengan jeda minimum antar
     * kirim supaya endpoint ini tidak bisa dipakai untuk spam ke email
     * orang lain. Sengaja TIDAK mensyaratkan kode lama masih aktif -
     * resend adalah cara utama pemulihan saat kode sebelumnya sudah
     * kedaluwarsa atau kehabisan percobaan, jadi baris OTP-nya di sini
     * "dihidupkan lagi" dengan kode baru, bukan dicari yang masih hidup.
     */
    public function resend(string $challenge, string $purpose): OtpCode
    {
        $otp = OtpCode::with('user')
            ->where('challenge', $challenge)
            ->where('purpose', $purpose)
            ->first();

        if (!$otp) {
            throw new OtpException('Sesi verifikasi tidak ditemukan. Ulangi dari awal.', 410);
        }

        if ($otp->last_sent_at && $otp->last_sent_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS) {
            $wait = self::RESEND_COOLDOWN_SECONDS - $otp->last_sent_at->diffInSeconds(now());
            throw new OtpException("Tunggu {$wait} detik lagi sebelum minta kode baru.", 429);
        }

        $code = $this->generateCode();
        $otp->update([
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            'attempts' => 0,
            'consumed_at' => null,
            'last_sent_at' => now(),
        ]);

        $this->sendMail($otp->user, $code, $purpose, $otp);

        return $otp;
    }

    /**
     * Verifikasi kode yang diketik user. Mengembalikan User kalau benar,
     * melempar OtpException dengan pesan siap-tampil kalau salah,
     * kedaluwarsa, atau sudah kehabisan jatah percobaan.
     */
    public function verify(string $challenge, string $code, string $purpose): User
    {
        $otp = $this->findActive($challenge, $purpose);

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->update(['consumed_at' => now()]); // matikan supaya tidak terus-terusan dicoba (brute force)
            throw new OtpException('Terlalu banyak percobaan salah. Minta kode baru.', 429);
        }

        if (!Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');
            $sisa = self::MAX_ATTEMPTS - $otp->attempts;
            throw new OtpException(
                $sisa > 0 ? "Kode OTP salah. Sisa percobaan: {$sisa}." : 'Kode OTP salah. Minta kode baru.',
                422
            );
        }

        $otp->update(['consumed_at' => now()]);

        return $otp->user;
    }

    private function findActive(string $challenge, string $purpose): OtpCode
    {
        $otp = OtpCode::with('user')
            ->where('challenge', $challenge)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->first();

        if (!$otp) {
            throw new OtpException('Sesi verifikasi tidak ditemukan atau sudah dipakai. Ulangi dari awal.', 410);
        }

        if ($otp->expires_at->isPast()) {
            $otp->update(['consumed_at' => now()]);
            throw new OtpException('Kode OTP sudah kedaluwarsa. Minta kode baru.', 410);
        }

        return $otp;
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    private function sendMail(User $user, string $code, string $purpose, OtpCode $otp): void
    {
        try {
            Mail::to($user->email)->send(new OtpMail($user, $code, $purpose));
        } catch (\Throwable $e) {
            // Jangan tinggalkan kode yang tidak mungkin pernah sampai ke user -
            // kalau dibiarkan, user akan terjebak menunggu email yang tidak akan datang.
            $otp->delete();
            Log::error('Gagal mengirim email OTP: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'purpose' => $purpose,
            ]);
            throw new OtpException('Gagal mengirim kode OTP ke email Anda. Coba lagi beberapa saat.', 503);
        }
    }

    // Samarkan email untuk ditampilkan di UI ("ka***@gmail.com") tanpa
    // membocorkan alamat lengkap ke siapa pun yang bisa melihat layar.
    public static function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = min(2, strlen($local));
        $masked = substr($local, 0, $visible) . str_repeat('*', max(strlen($local) - $visible, 3));

        return $masked . '@' . $domain;
    }
}
