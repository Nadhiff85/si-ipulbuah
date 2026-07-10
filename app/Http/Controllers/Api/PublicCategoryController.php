<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class PublicCategoryController extends Controller
{
    // Daftar kategori aktif untuk section "Jelajahi Kategori" di Beranda publik (tanpa login)
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'type', 'image']);

        return response()->json(['categories' => $categories]);
    }
}
