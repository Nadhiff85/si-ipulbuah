<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\Wishlist;
use App\Services\ImageUploadService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsapp,
        private ImageUploadService $imageUpload,
    ) {}

    // Manajemen Produk (fitur B.2): CRUD lengkap + multi-foto + varian + label
    public function index(Request $request)
    {
        $products = Product::with(['category', 'images'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->latest()->paginate(15);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);

        $product = Product::create($data);
        $this->syncImages($request, $product);
        $this->syncVariants($request, $product);

        return response()->json(['product' => $product->load('images', 'variants')], 201);
    }

    public function show(Product $product)
    {
        return response()->json(['product' => $product->load('images', 'variants', 'category')]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product->id);

        $stockBefore = $product->stock;
        $wasOutOfStock = $stockBefore === 0;

        $product->update($data);

        // Log perubahan stok (fitur B.14) jika stok BENAR-BENAR berubah.
        // Cast ke int dulu sebelum dibandingkan, karena $stockBefore datang dari
        // database (integer asli), sedangkan $product->stock setelah update()
        // berasal dari input form (selalu string), jadi "10" vs 10 bisa keliru
        // dianggap berubah kalau dibandingkan pakai !== tanpa cast.
        if ((int) $stockBefore !== (int) $product->stock) {
            StockLog::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'stock_before' => $stockBefore,
                'stock_after' => $product->stock,
                'change' => (int) $product->stock - (int) $stockBefore,
                // Tidak pakai default parameter di input() karena field kosong
                // otomatis dikonversi jadi null oleh middleware Laravel, dan
                // default di input() cuma jalan kalau key-nya tidak ada sama
                // sekali, bukan kalau ada tapi bernilai null. Pakai fallback
                // manual di sini supaya kolom NOT NULL di database tetap aman.
                'reason' => $request->input('stock_change_reason') ?: 'Penyesuaian oleh Admin',
            ]);

            // Notifikasi Stok (fitur A.12): beritahu pelanggan yang menunggu jika produk kembali tersedia
            if ($wasOutOfStock && $product->stock > 0) {
                $waitingUsers = Wishlist::where('product_id', $product->id)
                    ->where('notify_when_available', true)->with('user')->get();

                foreach ($waitingUsers as $wishlist) {
                    $this->whatsapp->send(
                        $wishlist->user->phone,
                        "Kabar baik! Produk \"{$product->name}\" yang Anda tunggu sudah tersedia kembali di IPUL BUAH 🍊",
                        'stock_available',
                        $wishlist->user->id
                    );
                    $wishlist->update(['notify_when_available' => false]);
                }
            }
        }

        if ($request->hasFile('images')) {
            $this->syncImages($request, $product);
        }
        if ($request->filled('variants')) {
            $product->variants()->delete();
            $this->syncVariants($request, $product);
        }

        return response()->json(['product' => $product->load('images', 'variants')]);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    private function validateProduct(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'origin_region' => 'nullable|string|max:255',
            'origin_type' => 'required|in:lokal,impor',
            'unit' => 'required|in:kg,pcs',
            'price_unit' => 'required|numeric|min:0',
            'price_wholesale' => 'nullable|numeric|min:0',
            'wholesale_min_qty' => 'nullable|integer|min:1',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'freshness_days' => 'nullable|integer|min:1',
            'stock_in_date' => 'nullable|date',
            'storage_tips' => 'nullable|string',
            'labels' => 'nullable|array', // ["best_seller","promo","musiman","segar"]
            'is_seasonal' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);
    }

    // Upload & optimasi foto produk memakai Intervention Image (resize + kompres)
    private function syncImages(Request $request, Product $product): void
    {
        if (!$request->hasFile('images')) return;

        foreach ($request->file('images') as $index => $file) {
            $filename = 'products/' . uniqid() . '.webp';
            $path = $this->imageUpload->saveAsWebp($file, $filename, maxWidth: 1000, quality: 80);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }

    // Varian ukuran (kecil/sedang/besar) - fitur B.2
    private function syncVariants(Request $request, Product $product): void
    {
        foreach ($request->input('variants', []) as $variant) {
            ProductVariant::create([
                'product_id' => $product->id,
                'size' => $variant['size'],
                'price_adjustment' => $variant['price_adjustment'] ?? 0,
                'stock' => $variant['stock'] ?? 0,
            ]);
        }
    }
}