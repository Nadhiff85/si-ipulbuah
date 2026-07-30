<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Buah Lokal', 'type' => 'lokal', 'sort_order' => 1],
            ['name' => 'Buah Impor', 'type' => 'impor', 'sort_order' => 2],
            ['name' => 'Buah Musiman', 'type' => 'musiman', 'sort_order' => 3],
            // IPUL BUAH hanya menjual buah utuh + semangka/melon yang dibelah
            // (bukan buah potong dadu dalam wadah, bukan jus/olahan).
            ['name' => 'Buah Belah', 'type' => 'lokal', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']) . '-' . Str::random(4),
                    'type' => $category['type'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }
    }
}