<?php

namespace Database\Seeders;

use App\Models\DeliveryRegion;
use Illuminate\Database\Seeder;

class DeliveryRegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['regency' => 'Kota Palu', 'district' => 'Palu Barat', 'shipping_cost' => 10000],
            ['regency' => 'Kota Palu', 'district' => 'Palu Selatan', 'shipping_cost' => 10000],
            ['regency' => 'Kota Palu', 'district' => 'Palu Timur', 'shipping_cost' => 12000],
            ['regency' => 'Kota Palu', 'district' => 'Palu Utara', 'shipping_cost' => 15000],
            ['regency' => 'Kota Palu', 'district' => 'Tatanga', 'shipping_cost' => 12000],
            ['regency' => 'Kota Palu', 'district' => 'Ulujadi', 'shipping_cost' => 15000],
            ['regency' => 'Kota Palu', 'district' => 'Mantikulore', 'shipping_cost' => 15000],
            ['regency' => 'Kota Palu', 'district' => 'Tawaeli', 'shipping_cost' => 18000],
            ['regency' => 'Kabupaten Sigi', 'district' => 'Sigi Biromaru', 'shipping_cost' => 20000],
            ['regency' => 'Kabupaten Sigi', 'district' => 'Dolo', 'shipping_cost' => 22000],
            ['regency' => 'Kabupaten Donggala', 'district' => 'Banawa', 'shipping_cost' => 20000],
            ['regency' => 'Kabupaten Donggala', 'district' => 'Labuan', 'shipping_cost' => 25000],
        ];

        foreach ($regions as $region) {
            DeliveryRegion::firstOrCreate($region);
        }
    }
}
