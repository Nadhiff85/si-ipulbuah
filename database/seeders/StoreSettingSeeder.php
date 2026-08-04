<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    // Memastikan selalu ada 1 baris store_settings & system_settings sejak awal,
    // supaya endpoint publik (Beranda, dll) tidak error 404/null.
    public function run(): void
    {
        StoreSetting::firstOrCreate([], [
            'store_name' => 'IPUL BUAH',
            'tagline' => 'Segar Setiap Hari, Sehat untuk Keluarga',
            'address' => 'Jl. Kemiri No. 47, Siranindi, Kec. Palu Barat, Kota Palu, Sulawesi Tengah, 94111',
            'whatsapp_number' => '6285244189949',
            'operating_hours' => ['buka' => '08:00', 'tutup' => '17:00'],
            'min_order_delivery' => 50000,
            'google_maps_link' => 'https://maps.app.goo.gl/Ux62N1vWpbXwpd8y7',
        ]);

        SystemSetting::firstOrCreate([], [
            'session_timeout_minutes' => 60,
            'max_login_attempts' => 5,
            'rate_limit_per_minute' => 60,
            'maintenance_mode' => false,
            'max_freshness_days_default' => 7,
            'delivery_globally_enabled' => true,
        ]);
    }
}