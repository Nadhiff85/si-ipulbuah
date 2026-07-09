<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\StoreSetting;

class StoreSettingsController extends Controller
{
    // Endpoint publik - dipakai Beranda, Footer, halaman Kontak & Tentang Kami (tanpa perlu login)
    public function show()
    {
        $settings = StoreSetting::first();

        if (!$settings) {
            return response()->json(['message' => 'Pengaturan toko belum diatur.'], 404);
        }

        return response()->json([
            'storeName' => $settings->store_name,
            'tagline' => $settings->tagline,
            'aboutContent' => $settings->about_content,
            'address' => $settings->address,
            'whatsappNumber' => $settings->whatsapp_number,
            'bankAccounts' => $settings->bank_accounts,
            'qrisImage' => $settings->qris_image,
            'operatingHours' => $settings->operating_hours,
            'logo' => $settings->logo,
        ]);
    }

    // FAQ publik (fitur A.19) - dikelola Admin lewat Manajemen Konten
    public function faqs()
    {
        return response()->json(['faqs' => Faq::where('is_active', true)->orderBy('sort_order')->get()]);
    }
}
