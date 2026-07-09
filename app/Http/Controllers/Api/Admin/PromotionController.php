<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    // Manajemen Promo (fitur B.7): diskon per produk/kategori, periode aktif
    public function index()
    {
        return response()->json(['promotions' => Promotion::latest()->get()]);
    }

    public function store(Request $request)
    {
        $promotion = Promotion::create($this->validateData($request));
        return response()->json(['promotion' => $promotion], 201);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $promotion->update($this->validateData($request));
        return response()->json(['promotion' => $promotion]);
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response()->json(['message' => 'Promo dihapus.']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percent,nominal',
            'discount_value' => 'required|numeric|min:0',
            'scope' => 'required|in:product,category',
            'target_id' => 'required|integer',
            'min_purchase_qty' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);
    }
}
