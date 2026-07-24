<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;

class PublicReviewController extends Controller
{
    /**
     * Ulasan pelanggan terbaru untuk Beranda publik (tanpa perlu login).
     * Hanya ulasan berstatus "approved" (sudah dimoderasi admin) yang tampil,
     * dan cuma field aman (nama depan pelanggan, bukan profil lengkap).
     */
    public function recent()
    {
        $reviews = Review::where('status', 'approved')
            ->whereNotNull('comment')
            ->with(['user:id,name', 'product:id,name'])
            ->latest()
            ->limit(6)
            ->get(['id', 'rating', 'comment', 'user_id', 'product_id', 'created_at']);

        return response()->json([
            'reviews' => $reviews->map(fn ($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'customer_name' => explode(' ', $r->user->name ?? 'Pelanggan')[0],
                'product_name' => $r->product->name ?? null,
                'created_at' => $r->created_at,
            ]),
        ]);
    }
}
