<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Manajemen Ulasan (fitur B.9): setujui/tolak, balas ulasan
    public function index(Request $request)
    {
        $reviews = Review::with(['user', 'product'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15);

        return response()->json($reviews);
    }

    public function moderate(Request $request, Review $review)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);
        $review->update(['status' => $request->status]);

        return response()->json(['review' => $review]);
    }

    public function reply(Request $request, Review $review)
    {
        $request->validate(['admin_reply' => 'required|string|max:1000']);
        $review->update(['admin_reply' => $request->admin_reply]);

        return response()->json(['review' => $review]);
    }
}
