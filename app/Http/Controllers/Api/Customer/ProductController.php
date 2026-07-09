<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductView;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Katalog & Pencarian Produk (fitur A.1)
     * Filter: kategori, satuan, rentang harga, asal (lokal/impor), label, pencarian teks
     * Sorting: harga_termurah | harga_termahal | terbaru
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        if ($request->filled('origin_type')) {
            $query->where('origin_type', $request->origin_type); // lokal | impor
        }

        if ($request->filled('label')) {
            $query->whereJsonContains('labels', $request->label); // segar | best_seller | musiman | promo
        }

        if ($request->filled('min_price')) {
            $query->where('price_unit', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_unit', '<=', $request->max_price);
        }

        match ($request->get('sort', 'terbaru')) {
            'harga_termurah' => $query->orderBy('price_unit', 'asc'),
            'harga_termahal' => $query->orderBy('price_unit', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12);

        return response()->json($products);
    }

    /**
     * Detail Produk Lengkap (fitur A.2): galeri foto, varian, asal, estimasi
     * kesegaran, badge musiman, harga satuan & grosir otomatis, tips penyimpanan.
     */
    public function show(Request $request, string $slug)
    {
        $product = Product::with(['category', 'images', 'variants', 'reviews' => function ($q) {
            $q->where('status', 'approved')->with('user')->latest();
        }])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Catat riwayat browsing untuk Audit Trail Pelanggan (fitur Superadmin C.2)
        ProductView::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'ip_address' => $request->ip(),
            'viewed_at' => now(),
        ]);

        return response()->json([
            'product' => $product,
            'freshness_remaining' => $product->freshness_remaining,
            'wholesale' => $product->price_wholesale ? [
                'price' => $product->price_wholesale,
                'min_qty' => $product->wholesale_min_qty,
            ] : null,
        ]);
    }
}
