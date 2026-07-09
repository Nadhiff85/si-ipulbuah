<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRegion;
use Illuminate\Http\Request;

class DeliveryAreaController extends Controller
{
    // Manajemen Area Pengiriman (fitur B.8): Kota Palu, Kab. Sigi, Kab. Donggala
    public function index()
    {
        return response()->json(['regions' => DeliveryRegion::orderBy('regency')->orderBy('district')->get()]);
    }

    public function store(Request $request)
    {
        $region = DeliveryRegion::create($this->validateData($request));
        return response()->json(['region' => $region], 201);
    }

    public function update(Request $request, DeliveryRegion $region)
    {
        $region->update($this->validateData($request));
        return response()->json(['region' => $region]);
    }

    // Nonaktifkan sementara (misal: banjir/akses terganggu) tanpa menghapus data
    public function toggleActive(DeliveryRegion $region)
    {
        $region->update(['is_active' => !$region->is_active]);
        return response()->json(['region' => $region]);
    }

    public function destroy(DeliveryRegion $region)
    {
        $region->delete();
        return response()->json(['message' => 'Wilayah dihapus.']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'regency' => 'required|in:Kota Palu,Kabupaten Sigi,Kabupaten Donggala',
            'district' => 'required|string|max:255',
            'shipping_cost' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);
    }
}
