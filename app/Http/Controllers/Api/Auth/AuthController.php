<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\OtpException;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(private OtpService $otp) {}

    /**
     * Langkah 1 dari registrasi akun Pelanggan baru. Data disimpan &
     * kode OTP dikirim ke email untuk verifikasi kepemilikan - token
     * BELUM diterbitkan di sini, baru setelah verifyRegisterOtp() sukses
     * (pola yang sama seperti login()). Role admin/superadmin TIDAK bisa
     * didaftarkan lewat endpoint publik ini - hanya dibuat oleh Superadmin
     * lewat Manajemen Akun Admin.
     */
    public function register(Request $request)
    {
        $existing = User::where('email', $request->input('email'))->first();

        // Kalau email itu sudah PUNYA akun yang emailnya sudah terverifikasi,
        // tolak seperti biasa (aturan unique). Tapi kalau baris user itu ada
        // namun belum pernah selesai verifikasi OTP (mis. user menutup tab
        // sebelum masukkan kode), izinkan "melanjutkan" registrasi yang sama
        // dengan data terbaru, bukan gagal dengan pesan "email sudah dipakai".
        $emailRule = ($existing && !$existing->email_verified_at)
            ? 'required|email'
            : 'required|email|unique:users,email';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($existing) {
            $existing->forceFill([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ])->save();
            $user = $existing;
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('pelanggan');

            // Setiap pelanggan baru otomatis punya 1 keranjang kosong
            Cart::create(['user_id' => $user->id]);
        }

        try {
            $otp = $this->otp->issue($user, 'register', $request->ip());
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json([
            'otp_required' => true,
            'challenge' => $otp->challenge,
            'email_hint' => OtpService::maskEmail($user->email),
            'expires_in' => OtpService::EXPIRES_MINUTES * 60,
        ], 201);
    }

    /**
     * Langkah 2 dari registrasi: verifikasi kode OTP yang membuktikan email
     * itu benar milik user. Baru di sini email_verified_at diisi & token
     * Sanctum diterbitkan - sebelum ini user tidak bisa login sama sekali
     * (lihat pengecekan email_verified_at di login()).
     */
    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'challenge' => 'required|string',
            'code' => 'required|string',
        ]);

        try {
            $user = $this->otp->verify($request->challenge, $request->code, 'register');
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        $user->forceFill(['email_verified_at' => now()])->save();

        $token = $user->createToken('ipulbuah-' . $user->getRoleNames()->first())->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    // Kirim ulang kode OTP registrasi (dipakai kalau email belum masuk / kadaluwarsa)
    public function resendRegisterOtp(Request $request)
    {
        $request->validate(['challenge' => 'required|string']);

        try {
            $this->otp->resend($request->challenge, 'register');
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json([
            'message' => 'Kode baru telah dikirim ke email Anda.',
            'expires_in' => OtpService::EXPIRES_MINUTES * 60,
        ]);
    }

    /**
     * Langkah 1 dari login (seluruh role: pelanggan, admin, superadmin).
     * Kredensial diverifikasi seperti biasa, tapi token BELUM diterbitkan
     * di sini - kalau email+password benar, kode OTP 6 digit dikirim ke
     * email user dan frontend diarahkan ke layar verifikasi (fitur 2FA).
     * Token baru diterbitkan di verifyOtp() setelah kode itu dikonfirmasi.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Enforce Maksimal Percobaan Login Gagal (fitur C.13) - dicek SEBELUM
        // OTP dikirim, supaya percobaan brute force password tidak ikut
        // memicu pengiriman email (potensi spam ke inbox orang lain).
        if ($user) {
            $settings = \App\Models\SystemSetting::firstOrCreate([]);
            $maxAttempts = $settings->max_login_attempts ?? 5;

            $recentFailedAttempts = LoginLog::where('user_id', $user->id)
                ->where('is_failed_attempt', true)
                ->where('created_at', '>=', now()->subMinutes(15))
                ->count();

            if ($recentFailedAttempts >= $maxAttempts) {
                return response()->json([
                    'message' => "Terlalu banyak percobaan login gagal. Akun sementara dikunci, coba lagi dalam 15 menit.",
                ], 429);
            }
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->logLoginAttempt($user, $request, failed: true);
            return response()->json(['message' => 'Email atau kata sandi salah.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun Anda telah dinonaktifkan. Hubungi admin toko.'], 403);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Email Anda belum diverifikasi. Silakan daftar ulang dengan email yang sama untuk menerima kode verifikasi.',
            ], 403);
        }

        try {
            $otp = $this->otp->issue($user, 'login', $request->ip());
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json([
            'otp_required' => true,
            'challenge' => $otp->challenge,
            'email_hint' => OtpService::maskEmail($user->email),
            'expires_in' => OtpService::EXPIRES_MINUTES * 60,
        ]);
    }

    /**
     * Langkah 2 dari login: verifikasi kode OTP yang dikirim ke email.
     * Kalau cocok, baru di sini token Sanctum diterbitkan - sama persis
     * dengan response login() versi lama (single-step) supaya frontend
     * lama tidak perlu tahu banyak perbedaan bentuk data.
     */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'challenge' => 'required|string',
            'code' => 'required|string',
        ]);

        try {
            $user = $this->otp->verify($request->challenge, $request->code, 'login');
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        $token = $user->createToken('ipulbuah-' . $user->getRoleNames()->first())->plainTextToken;

        $user->forceFill(['last_login_at' => now()])->save();
        $this->logLoginAttempt($user, $request, failed: false);

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    // Kirim ulang kode OTP login (dipakai kalau email belum masuk / kadaluwarsa)
    public function resendLoginOtp(Request $request)
    {
        $request->validate(['challenge' => 'required|string']);

        try {
            $this->otp->resend($request->challenge, 'login');
        } catch (OtpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        return response()->json([
            'message' => 'Kode baru telah dikirim ke email Anda.',
            'expires_in' => OtpService::EXPIRES_MINUTES * 60,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'role' => $user->getRoleNames()->first(), // pelanggan | admin | superadmin
            'outlet_id' => $user->outlet_id,
        ];
    }

    private function logLoginAttempt(?User $user, Request $request, bool $failed): void
    {
        if (!$user) return;

        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => $failed ? null : now(),
            'is_failed_attempt' => $failed,
        ]);
    }
}
