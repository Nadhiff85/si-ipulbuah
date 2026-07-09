<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Ambil isi keranjang milik pelanggan yang sedang login
    public function index(Request $request)
    {
        $cart = $request->user()->cart()->with([
            'items.product.images', 'items.hamper', 'items.variant',
        ])->firstOrCreate([]);

        return response()->json(['items' => $this->transformItems($cart->items)]);
    }

    // Tambah item ke keranjang (produk biasa, varian, atau parsel kustom)
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'hamper_id' => 'nullable|exists:hampers,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'qty' => 'required|numeric|min:0.5',
            'note' => 'nullable|string|max:255',
            'custom_hamper_config' => 'nullable|array',
        ]);

        // Minimal salah satu harus diisi: produk biasa, hamper, atau parsel kustom
        abort_if(
            !$request->product_id && !$request->hamper_id && !$request->custom_hamper_config,
            422,
            'Item keranjang tidak valid.'
        );

        $cart = $request->user()->cart()->firstOrCreate([]);

        // Cek stok tersedia sebelum menambahkan
        if ($request->product_id) {
            $product = Product::findOrFail($request->product_id);
            if ($product->stock < $request->qty) {
                return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
            }
        }

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'hamper_id' => $request->hamper_id,
            'product_variant_id' => $request->product_variant_id,
            'qty' => $request->qty,
            'note' => $request->note,
            'custom_hamper_config' => $request->custom_hamper_config,
        ]);

        return response()->json(['items' => $this->transformItems($cart->items()->with(['product.images', 'hamper', 'variant'])->get())], 201);
    }

    // Ubah kuantitas item
    public function update(Request $request, CartItem $item)
    {
        $this->authorizeOwnership($request, $item);

        $request->validate(['qty' => 'required|integer|min:1']);
        $item->update(['qty' => $request->qty]);

        return response()->json(['items' => $this->transformItems($item->cart->items()->with(['product.images', 'hamper', 'variant'])->get())]);
    }

    // Hapus item dari keranjang
    public function destroy(Request $request, CartItem $item)
    {
        $this->authorizeOwnership($request, $item);
        $cart = $item->cart;
        $item->delete();

        return response()->json(['items' => $this->transformItems($cart->items()->with(['product.images', 'hamper', 'variant'])->get())]);
    }

    private function authorizeOwnership(Request $request, CartItem $item): void
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403, 'Item ini bukan milik Anda.');
    }

    private function transformItems($items)
    {
        return $items->map(function (CartItem $item) {
            // Parsel kustom: harga dihitung otomatis dari pilihan buah+wadah+kartu (fitur A.4)
            if ($item->custom_hamper_config && isset($item->custom_hamper_config['estimated_price'])) {
                return [
                    'id' => $item->id,
                    'name' => 'Parsel Kustom',
                    'image' => null,
                    'qty' => $item->qty,
                    'price' => $item->custom_hamper_config['estimated_price'],
                    'note' => $item->note,
                    'variant' => null,
                    'custom_hamper_config' => $item->custom_hamper_config,
                ];
            }

            $unitPrice = $item->product->price_unit ?? $item->hamper->base_price ?? 0;
            if ($item->variant) {
                $unitPrice += $item->variant->price_adjustment;
            }

            return [
                'id' => $item->id,
                'name' => $item->product->name ?? $item->hamper->name,
                'image' => $item->product?->images->first()?->image_path,
                'qty' => $item->qty,
                'price' => $unitPrice,
                'note' => $item->note,
                'variant' => $item->variant?->size,
                'custom_hamper_config' => $item->custom_hamper_config,
            ];
        });
    }
}
