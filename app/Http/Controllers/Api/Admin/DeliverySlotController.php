<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlot;
use Illuminate\Http\Request;

class DeliverySlotController extends Controller
{
    // Manajemen Jadwal Pengiriman (fitur B.18): atur kuota pesanan per slot waktu
    public function index()
    {
        return response()->json(['slots' => DeliverySlot::orderBy('start_time')->get()]);
    }

    public function update(Request $request, DeliverySlot $slot)
    {
        $data = $request->validate([
            'quota_per_day' => 'required|integer|min:1',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        $slot->update($data);
        return response()->json(['slot' => $slot]);
    }
}
