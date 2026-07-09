<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRegion;
use App\Models\DeliverySlot;

class DeliveryController extends Controller
{
    // Daftar slot waktu aktif (Pagi/Siang/Sore) - fitur A.17
    public function slots()
    {
        return response()->json(['slots' => DeliverySlot::where('is_active', true)->get()]);
    }

    // Daftar wilayah pengiriman aktif: Kota Palu, Kab. Sigi, Kab. Donggala - dipakai form alamat
    public function regions()
    {
        return response()->json([
            'regions' => DeliveryRegion::where('is_active', true)->orderBy('regency')->orderBy('district')->get(),
        ]);
    }
}
