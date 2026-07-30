<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hamper;
use App\Models\HamperCard;
use App\Models\HamperContainer;
use App\Models\Product;

class HamperController extends Controller
{
    // Daftar paket buah/hampers yang sudah dikurasi admin (fitur A.4)
    public function index()
    {
        $hampers = Hamper::where('is_active', true)->with('items.product')->get();
        return response()->json(['hampers' => $hampers]);
    }

    public function show(Hamper $hamper)
    {
        $hamper->load('items.product.images');
        return response()->json(['hamper' => $hamper]);
    }

    // Data pendukung untuk wizard Parsel Kustom: pilihan buah, wadah, kartu ucapan
    public function customOptions()
    {
        return response()->json([
            // Ikut kirim foto produk supaya wizard Parsel Kustom bisa menampilkan
            // gambar buah asli (kartu pilihan & pratinjau isi keranjang).
            'products' => Product::where('is_active', true)
                ->with(['images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')])
                ->get(['id', 'name', 'price_unit', 'unit']),
            'containers' => HamperContainer::where('is_active', true)->get(),
            'cards' => HamperCard::where('is_active', true)->get(),
        ]);
    }
}
