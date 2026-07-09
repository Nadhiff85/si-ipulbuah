<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    // Pengaturan Sistem gabungan: Masa Aktif Produk (C.9), Wilayah Global (C.7), Keamanan (C.13)
    public function show()
    {
        return response()->json(['settings' => SystemSetting::firstOrCreate([])]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'session_timeout_minutes' => 'required|integer|min:5',
            'max_login_attempts' => 'required|integer|min:1',
            'rate_limit_per_minute' => 'required|integer|min:1',
            'maintenance_mode' => 'boolean',
            'max_freshness_days_default' => 'required|integer|min:1',
            'delivery_globally_enabled' => 'boolean',
        ]);

        $settings = SystemSetting::firstOrCreate([]);
        $settings->update($data);

        return response()->json(['settings' => $settings]);
    }

    // Toggle cepat: matikan/aktifkan SEMUA layanan delivery secara global (mis. bencana alam) - fitur C.7
    public function toggleGlobalDelivery()
    {
        $settings = SystemSetting::firstOrCreate([]);
        $settings->update(['delivery_globally_enabled' => !$settings->delivery_globally_enabled]);

        return response()->json(['settings' => $settings]);
    }
}
