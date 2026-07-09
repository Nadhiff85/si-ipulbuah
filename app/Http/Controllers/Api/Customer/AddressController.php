<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    // Daftar alamat tersimpan milik pelanggan (fitur A.13 - bisa >1 alamat dalam Kota Palu/Sigi/Donggala)
    public function index(Request $request)
    {
        return response()->json(['addresses' => $request->user()->addresses()->with('deliveryRegion')->get()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'full_address' => 'required|string',
            'delivery_region_id' => 'required|exists:delivery_regions,id',
            'is_default' => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($request->all());

        return response()->json(['address' => $address], 201);
    }

    public function update(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($request->all());
        return response()->json(['address' => $address]);
    }

    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();
        return response()->json(['message' => 'Alamat dihapus.']);
    }
}
