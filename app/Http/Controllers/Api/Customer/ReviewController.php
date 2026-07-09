<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Ulasan & Rating setelah pesanan selesai (fitur A.11)
    public function store(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $orderItem = OrderItem::with('order')->findOrFail($request->order_item_id);
        abort_unless($orderItem->order->user_id === $request->user()->id, 403);
        abort_unless($orderItem->order->status === 'selesai', 422, 'Ulasan hanya bisa diberikan setelah pesanan selesai.');

        $review = Review::create([
            'order_item_id' => $orderItem->id,
            'user_id' => $request->user()->id,
            'product_id' => $orderItem->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending', // menunggu moderasi admin sebelum tampil publik
        ]);

        $orderItem->update(['is_reviewed' => true]);

        return response()->json(['review' => $review], 201);
    }
}
