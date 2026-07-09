<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class DynamicRateLimit
{
    // Menerapkan Rate Limiting (fitur C.13) memakai nilai yang diatur Superadmin,
    // bukan angka statis di kode - berlaku untuk seluruh endpoint API.
    public function handle(Request $request, Closure $next)
    {
        $settings = SystemSetting::firstOrCreate([]);
        $maxAttempts = $settings->rate_limit_per_minute ?? 60;

        $key = 'api-rate:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json(['message' => 'Terlalu banyak permintaan. Coba lagi sebentar.'], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
