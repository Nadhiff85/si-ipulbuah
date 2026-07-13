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
            ['name' => 'Buah Potong', 'type' => 'lokal', 'sort_order' => 4],
            ['name' => 'Jus & Olahan Buah', 'type' => 'lokal', 'sort_order' => 5],
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