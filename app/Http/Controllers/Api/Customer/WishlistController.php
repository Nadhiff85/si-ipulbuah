<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // Daftar wishlist pelanggan (fitur A.10)
    public function index(Request $request)
    {
        $wishlists = $request->user()->wishlists()->with('product.images')->get();
        return response()->json(['wishlists' => $wishlists]);
    }

    // Tambah produk ke wishlist
    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['wishlist' => $wishlist], 201);
    }

    // Hapus dari wishlist
    public function destroy(Request $request, Wishlist $wishlist)
    {
        abort_unless($wishlist->user_id === $request->user()->id, 403);
        $wishlist->delete();
        return response()->json(['message' => 'Dihapus dari wishlist.']);
    }

    // Tombol "Beritahu Saya" - aktifkan notifikasi saat stok produk tersedia kembali
    public function notifyMe(Request $request, Wishlist $wishlist)
    {
        abort_unless($wishlist->user_id === $request->user()->id, 403);
        $wishlist->update(['notify_when_available' => true]);
        return response()->json(['message' => 'Anda akan diberitahu saat stok tersedia.']);
    }
}
