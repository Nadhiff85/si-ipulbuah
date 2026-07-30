<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\OtpException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Alur "Lupa Kata Sandi" berbasis OTP email (3 langkah):
 *   1. forgot()  - user masukkan email -> kode OTP dikirim
 *   2. resend()  - opsional, kirim ulang kalau belum masuk
 *   3. reset()   - user masukkan kode + kata sandi baru -> password diganti
 */
class PasswordResetController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Aplikasi kompetisi ini memilih pesan eksplisit (bukan pesan generik
            // "kalau email terdaftar akan dikirim...") supaya alurnya jelas untuk
            // demo - trade-off: sedikit membocorkan apakah sebuah email terdaftar.
            return response()->json(['message' => 'Email tidak terdaftar di IPUL BUAH.'], 404);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun ini telah dinonaktifkan. Hubungi admin toko.'], 403);
        }

        try {
            $otp = $this->otp->issue($user, 'reset_password', $request->ip());
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json([
            'challenge' => $otp->challenge,
            'email_hint' => OtpService::maskEmail($user->email),
            'expires_in' => OtpService::EXPIRES_MINUTES * 60,
        ]);
    }

    public function resend(Request $request)
    {
        $request->validate(['challenge' => 'required|string']);

        try {
            $this->otp->resend($request->challenge, 'reset_password');
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json([
            'message' => 'Kode baru telah dikirim ke email Anda.',
            'expires_in' => OtpService::EXPIRES_MINUTES * 60,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'challenge' => 'required|string',
            'code' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $user = $this->otp->verify($request->challenge, $request->code, 'reset_password');
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        $user->forceFill(['password' => Hash::make($request->password)])->save();

        // Cabut semua token yang sudah terbit sebelumnya - kalau perangkat lain
        // sedang login (atau akun sempat dibobol), semua dipaksa login ulang
        // memakai kata sandi baru begitu password direset.
        $user->tokens()->delete();

        return response()->json(['message' => 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.']);
    }
}
