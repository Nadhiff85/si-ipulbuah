<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class CategoryController extends Controller
{
    // Manajemen Kategori (fitur B.3) - termasuk upload foto/ikon kategori
    public function index()
    {
        return response()->json(['categories' => Category::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        $category = Category::create($data);

        if ($request->hasFile('image_file')) {
            $category->update(['image' => $this->storeImage($request)]);
        }

        return response()->json(['category' => $category], 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('image_file')) {
            $data['image'] = $this->storeImage($request);
        }

        $category->update($data);
        return response()->json(['category' => $category]);
    }

    public function destroy(Category $category)
    {
        abort_if($category->products()->exists(), 422, 'Kategori masih memiliki produk, tidak bisa dihapus.');
        $category->delete();
        return response()->json(['message' => 'Kategori dihapus.']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:lokal,impor,musiman',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
    }

    // Upload & optimasi foto/ikon kategori memakai Intervention Image
    private function storeImage(Request $request): string
    {
        $filename = 'categories/' . uniqid() . '.webp';

        Image::read($request->file('image_file'))
            ->scaleDown(width: 400)
            ->toWebp(quality: 80)
            ->save(storage_path('app/public/' . $filename));

        return '/storage/' . $filename;
    }
}
