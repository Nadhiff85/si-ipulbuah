<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    // Data contoh supaya katalog tidak kosong di instalasi baru (fresh install/demo).
    // Tanpa seeder ini, php artisan migrate --seed menghasilkan toko tanpa satu pun
    // produk untuk dijelajahi/dibeli - risiko besar saat demo di depan juri.
    public function run(): void
    {
        $products = [
            ['category' => 'Buah Lokal', 'name' => 'Mangga Harum Manis', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'kg', 'price' => 22500, 'stock' => 60, 'freshness' => 5, 'labels' => ['best_seller'], 'featured' => true],
            ['category' => 'Buah Lokal', 'name' => 'Jeruk Baby Pontianak', 'origin' => 'Pontianak', 'origin_type' => 'lokal', 'unit' => 'kg', 'price' => 18000, 'stock' => 50, 'freshness' => 7, 'labels' => ['segar'], 'featured' => true],
            ['category' => 'Buah Lokal', 'name' => 'Pisang Kepok', 'origin' => 'Sigi', 'origin_type' => 'lokal', 'unit' => 'sisir', 'price' => 15000, 'stock' => 40, 'freshness' => 4, 'labels' => ['segar'], 'featured' => false],
            ['category' => 'Buah Lokal', 'name' => 'Pepaya California', 'origin' => 'Donggala', 'origin_type' => 'lokal', 'unit' => 'kg', 'price' => 12000, 'stock' => 35, 'freshness' => 5, 'labels' => [], 'featured' => false],
            ['category' => 'Buah Lokal', 'name' => 'Nanas Palu', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'pcs', 'price' => 10000, 'stock' => 30, 'freshness' => 6, 'labels' => ['segar'], 'featured' => false],
            ['category' => 'Buah Lokal', 'name' => 'Rambutan Binjai', 'origin' => 'Sigi', 'origin_type' => 'lokal', 'unit' => 'kg', 'price' => 20000, 'stock' => 25, 'freshness' => 4, 'labels' => [], 'featured' => false],

            ['category' => 'Buah Impor', 'name' => 'Apel Fuji', 'origin' => 'Tiongkok', 'origin_type' => 'impor', 'unit' => 'kg', 'price' => 38000, 'stock' => 40, 'freshness' => 14, 'labels' => ['best_seller'], 'featured' => true],
            ['category' => 'Buah Impor', 'name' => 'Anggur Red Globe', 'origin' => 'Australia', 'origin_type' => 'impor', 'unit' => 'kg', 'price' => 65000, 'stock' => 25, 'freshness' => 10, 'labels' => ['best_seller'], 'featured' => true],
            ['category' => 'Buah Impor', 'name' => 'Jeruk Sunkist', 'origin' => 'Amerika Serikat', 'origin_type' => 'impor', 'unit' => 'kg', 'price' => 42000, 'stock' => 30, 'freshness' => 12, 'labels' => [], 'featured' => false],
            ['category' => 'Buah Impor', 'name' => 'Kiwi Zespri', 'origin' => 'Selandia Baru', 'origin_type' => 'impor', 'unit' => 'pcs', 'price' => 8000, 'stock' => 45, 'freshness' => 10, 'labels' => [], 'featured' => false],

            ['category' => 'Buah Musiman', 'name' => 'Durian Montong', 'origin' => 'Sigi', 'origin_type' => 'lokal', 'unit' => 'pcs', 'price' => 85000, 'stock' => 15, 'freshness' => 3, 'labels' => ['musiman'], 'featured' => true, 'seasonal' => true],
            ['category' => 'Buah Musiman', 'name' => 'Duku Palembang', 'origin' => 'Palembang', 'origin_type' => 'lokal', 'unit' => 'kg', 'price' => 28000, 'stock' => 20, 'freshness' => 4, 'labels' => ['musiman'], 'featured' => false, 'seasonal' => true],
            ['category' => 'Buah Musiman', 'name' => 'Mangga Manalagi', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'kg', 'price' => 25000, 'stock' => 20, 'freshness' => 5, 'labels' => ['musiman'], 'featured' => false, 'seasonal' => true],

            ['category' => 'Buah Potong', 'name' => 'Semangka Potong', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'pack', 'price' => 15000, 'stock' => 30, 'freshness' => 2, 'labels' => ['segar'], 'featured' => true],
            ['category' => 'Buah Potong', 'name' => 'Melon Potong', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'pack', 'price' => 17000, 'stock' => 25, 'freshness' => 2, 'labels' => ['segar'], 'featured' => false],
            ['category' => 'Buah Potong', 'name' => 'Rujak Buah Segar', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'pack', 'price' => 20000, 'stock' => 20, 'freshness' => 1, 'labels' => ['promo'], 'featured' => false],

            ['category' => 'Jus & Olahan Buah', 'name' => 'Jus Alpukat', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'botol', 'price' => 18000, 'stock' => 30, 'freshness' => 2, 'labels' => [], 'featured' => false],
            ['category' => 'Jus & Olahan Buah', 'name' => 'Jus Mangga', 'origin' => 'Palu', 'origin_type' => 'lokal', 'unit' => 'botol', 'price' => 16000, 'stock' => 30, 'freshness' => 2, 'labels' => [], 'featured' => false],
        ];

        foreach ($products as $p) {
            $category = Category::where('name', $p['category'])->first();
            if (!$category) {
                continue;
            }

            $slug = Str::slug($p['name']);

            Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $p['name'],
                    'description' => $p['name'] . ' segar pilihan, langsung dari ' . $p['origin'] . '.',
                    'origin_region' => $p['origin'],
                    'origin_type' => $p['origin_type'],
                    'unit' => $p['unit'],
                    'price_unit' => $p['price'],
                    'cost_price' => round($p['price'] * 0.7),
                    'stock' => $p['stock'],
                    'min_stock_alert' => 5,
                    'freshness_days' => $p['freshness'],
                    'stock_in_date' => now(),
                    'storage_tips' => 'Simpan di tempat sejuk, hindari sinar matahari langsung.',
                    'labels' => $p['labels'],
                    'is_seasonal' => $p['seasonal'] ?? false,
                    'is_active' => true,
                    'is_featured' => $p['featured'],
                    'rating_avg' => 0,
                    'rating_count' => 0,
                    'sold_count' => 0,
                ]
            );
        }
    }
}
