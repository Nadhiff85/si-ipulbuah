<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Faq;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    // Manajemen Konten (fitur B.11): banner/slider beranda & FAQ
    public function banners()
    {
        return response()->json(['banners' => Banner::orderBy('sort_order')->get()]);
    }

    public function storeBanner(Request $request)
    {
        $banner = Banner::create($request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|string',
            'link' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]));

        return response()->json(['banner' => $banner], 201);
    }

    public function destroyBanner(Banner $banner)
    {
        $banner->delete();
        return response()->json(['message' => 'Banner dihapus.']);
    }

    public function faqs()
    {
        return response()->json(['faqs' => Faq::orderBy('sort_order')->get()]);
    }

    public function storeFaq(Request $request)
    {
        $faq = Faq::create($request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]));

        return response()->json(['faq' => $faq], 201);
    }

    public function updateFaq(Request $request, Faq $faq)
    {
        $faq->update($request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]));

        return response()->json(['faq' => $faq]);
    }

    public function destroyFaq(Faq $faq)
    {
        $faq->delete();
        return response()->json(['message' => 'FAQ dihapus.']);
    }
}
