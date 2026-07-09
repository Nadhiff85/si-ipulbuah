<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hamper;
use App\Models\HamperItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HamperController extends Controller
{
    // Manajemen Paket/Hampers (fitur B.4) - termasuk aktivasi opsi kustom parsel
    public function index()
    {
        return response()->json(['hampers' => Hamper::with('items.product')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        $hamper = Hamper::create($data);

        $this->syncItems($request, $hamper);

        return response()->json(['hamper' => $hamper->load('items.product')], 201);
    }

    public function update(Request $request, Hamper $hamper)
    {
        $hamper->update($this->validateData($request));

        if ($request->filled('items')) {
            $hamper->items()->delete();
            $this->syncItems($request, $hamper);
        }

        return response()->json(['hamper' => $hamper->load('items.product')]);
    }

    public function destroy(Hamper $hamper)
    {
        $hamper->delete();
        return response()->json(['message' => 'Paket dihapus.']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_custom_allowed' => 'boolean',
            'is_active' => 'boolean',
        ]);
    }

    private function syncItems(Request $request, Hamper $hamper): void
    {
        foreach ($request->input('items', []) as $item) {
            HamperItem::create([
                'hamper_id' => $hamper->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'] ?? 1,
            ]);
        }
    }
}
