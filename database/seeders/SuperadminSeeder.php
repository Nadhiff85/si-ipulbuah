<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        // Email akun seed staff DIHARUSKAN alamat asli yang bisa dibuka -
        // sejak login memakai OTP (2FA) via email, domain palsu seperti
        // "@ipulbuah.com" membuat kode OTP tidak akan pernah sampai dan
        // akun jadi tidak bisa dipakai login sama sekali. Dua-duanya
        // memakai alias "+" Gmail supaya tetap unik di kolom email tapi
        // sama-sama masuk ke satu inbox yang sama.
        // email_verified_at diisi langsung (bukan lewat OTP registrasi) -
        // akun staff dibuat oleh sistem/seeder, bukan mendaftar sendiri
        // lewat form publik, jadi tidak perlu membuktikan kepemilikan email.
        $superadmin = User::firstOrCreate(
            ['email' => 'khaidirakmal236+superadmin@gmail.com'],
            [
                'name' => 'Superadmin IPUL BUAH',
                'phone' => '6281200000000',
                'password' => Hash::make('IpulBuah#2026'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superadmin->assignRole('superadmin');

        $admin = User::firstOrCreate(
            ['email' => 'khaidirakmal236+admin@gmail.com'],
            [
                'name' => 'Admin Toko IPUL BUAH',
                'phone' => '6281200000001',
                'password' => Hash::make('AdminToko#2026'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');
    }
}
