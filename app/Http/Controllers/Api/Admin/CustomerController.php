<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Manajemen Pelanggan (fitur B.10): daftar, detail, nonaktifkan akun
    public function index(Request $request)
    {
        $customers = User::role('pelanggan')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->withCount('orders')
            ->latest()->paginate(15);

        return response()->json($customers);
    }

    public function show(User $customer)
    {
        $customer->load(['orders.items', 'reviews', 'addresses']);
        return response()->json(['customer' => $customer]);
    }

    public function toggleActive(User $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        return response()->json(['customer' => $customer]);
    }
}
