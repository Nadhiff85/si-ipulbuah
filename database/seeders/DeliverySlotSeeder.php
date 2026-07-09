<?php

namespace Database\Seeders;

use App\Models\DeliverySlot;
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [
            ['name' => 'Pagi', 'start_time' => '08:00', 'end_time' => '11:00', 'quota_per_day' => 15],
            ['name' => 'Siang', 'start_time' => '11:00', 'end_time' => '14:00', 'quota_per_day' => 15],
            ['name' => 'Sore', 'start_time' => '14:00', 'end_time' => '17:00', 'quota_per_day' => 15],
        ];

        foreach ($slots as $slot) {
            DeliverySlot::firstOrCreate(['name' => $slot['name']], $slot);
        }
    }
}
