<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class PublicProductController extends Controller
{
    /**
     * Produk Terlaris di Beranda publik (tanpa perlu login).
     * Dikurasi oleh Admin/Superadmin lewat toggle "is_featured" di Manajemen Produk.
     * Hanya field yang aman ditampilkan ke publik (harga & foto boleh, stok/cost_price tidak).
     */
    public function featured()
    {
        $products = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true)->limit(1)])
            ->orderByDesc('sold_count')
            ->limit(6)
            ->get(['id', 'name', 'slug', 'unit', 'price_unit', 'labels']);

        return response()->json(['products' => $products]);
    }
}
