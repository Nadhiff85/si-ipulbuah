<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperadminSeeder::class,
            CategorySeeder::class,
            DeliveryRegionSeeder::class,
            DeliverySlotSeeder::class,
            StoreSettingSeeder::class,
            ProductSeeder::class,
        ]);
    }
}