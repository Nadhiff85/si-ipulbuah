<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@ipulbuah.com'],
            [
                'name' => 'Superadmin IPUL BUAH',
                'phone' => '6281200000000',
                'password' => Hash::make('IpulBuah#2026'),
                'is_active' => true,
            ]
        );
        $superadmin->assignRole('superadmin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@ipulbuah.com'],
            [
                'name' => 'Admin Toko IPUL BUAH',
                'phone' => '6281200000001',
                'password' => Hash::make('AdminToko#2026'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');
    }
}
