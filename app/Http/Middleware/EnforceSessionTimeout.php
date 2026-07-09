<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;

class EnforceSessionTimeout
{
    // Menerapkan Durasi Session Timeout (fitur C.13): token Sanctum dianggap
    // kedaluwarsa jika tidak ada aktivitas melebihi durasi yang diatur Superadmin.
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token && $token->last_used_at) {
            $settings = SystemSetting::firstOrCreate([]);
            $timeoutMinutes = $settings->session_timeout_minutes ?? 60;

            if (now()->diffInMinutes($token->last_used_at) > $timeoutMinutes) {
                $token->delete();
                return response()->json(['message' => 'Sesi Anda telah berakhir, silakan masuk kembali.'], 401);
            }
        }

        return $next($request);
    }
}
