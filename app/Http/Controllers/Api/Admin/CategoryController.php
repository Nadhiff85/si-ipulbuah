<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Manajemen Kategori (fitur B.3)
    public function index()
    {
        return response()->json(['categories' => Category::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        $category = Category::create($data);

        return response()->json(['category' => $category], 201);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validateData($request));
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
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
    }
}
