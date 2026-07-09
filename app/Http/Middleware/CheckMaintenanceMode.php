<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    // Menerapkan Mode Maintenance (fitur C.13): blokir akses publik/pelanggan saat aktif,
    // admin & superadmin tetap bisa masuk untuk menyelesaikan perbaikan.
    public function handle(Request $request, Closure $next)
    {
        $settings = SystemSetting::firstOrCreate([]);

        if ($settings && $settings->maintenance_mode) {
            $user = $request->user();
            $isStaff = $user && $user->hasAnyRole(['admin', 'superadmin']);

            if (!$isStaff) {
                return response()->json([
                    'message' => 'Sistem sedang dalam perbaikan (maintenance). Silakan coba beberapa saat lagi.',
                ], 503);
            }
        }

        return $next($request);
    }
}
