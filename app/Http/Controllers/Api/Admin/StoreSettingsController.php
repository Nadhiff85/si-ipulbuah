<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class StoreSettingsController extends Controller
{
    // Pengaturan Toko (bagian dari fitur B & C.2): nama, alamat, WA, rekening, QRIS, jam operasional, min order
    public function show()
    {
        return response()->json(['settings' => StoreSetting::firstOrCreate([])]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'sometimes|required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'about_content' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_maps_link' => 'nullable|url',
            'whatsapp_number' => 'nullable|string|max:20',
            'whatsapp_api_key' => 'nullable|string',
            'bank_accounts' => 'nullable|array',
            'operating_hours' => 'nullable|array',
            'min_order_delivery' => 'nullable|numeric|min:0',
        ]);

        $settings = StoreSetting::firstOrCreate([]);
        $settings->update($data);

        return response()->json(['settings' => $settings]);
    }

    // Upload/update gambar QRIS statis toko (fitur B.21)
    public function updateQris(Request $request)
    {
        $request->validate(['qris_image' => 'required|image|max:2048']);
        $path = $request->file('qris_image')->store('qris', 'public');

        $settings = StoreSetting::firstOrCreate([]);
        $settings->update(['qris_image' => '/storage/' . $path]);

        return response()->json(['settings' => $settings]);
    }
}