<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registrasi akun Pelanggan baru.
     * Role admin/superadmin TIDAK bisa didaftarkan lewat endpoint publik ini -
     * hanya dibuat oleh Superadmin lewat Manajemen Akun Admin.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('pelanggan');

        // Setiap pelanggan baru otomatis punya 1 keranjang kosong
        Cart::create(['user_id' => $user->id]);

        $token = $user->createToken('ipulbuah-customer')->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login untuk seluruh role (pelanggan, admin, superadmin).
     * Role ditentukan dari data user, bukan dari input - frontend
     * memakai field `role` pada response untuk redirect ke dashboard yang sesuai.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Enforce Maksimal Percobaan Login Gagal (fitur C.13)
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

        $token = $user->createToken('ipulbuah-' . $user->getRoleNames()->first())->plainTextToken;

        $user->forceFill(['last_login_at' => now()])->save();
        $this->logLoginAttempt($user, $request, failed: false);

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
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
