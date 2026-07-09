<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['pelanggan', 'admin', 'superadmin'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $permissions = [
            'produk.lihat', 'produk.kelola',
            'kategori.kelola',
            'hampers.kelola',
            'promo.kelola',
            'pesanan.lihat', 'pesanan.kelola',
            'pembayaran.kelola',
            'pengiriman.kelola',
            'pelanggan.kelola',
            'ulasan.kelola',
            'konten.kelola',
            'laporan.lihat', 'laporan.ekspor',
            'admin.kelola',
            'audit-trail.lihat',
            'sistem.kelola',
            'backup.kelola',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::findByName('superadmin')->givePermissionTo(Permission::all());

        Role::findByName('admin')->givePermissionTo(
            Permission::whereNotIn('name', ['admin.kelola', 'audit-trail.lihat', 'sistem.kelola', 'backup.kelola'])->get()
        );

        Role::findByName('pelanggan')->givePermissionTo(['produk.lihat']);
    }
}
