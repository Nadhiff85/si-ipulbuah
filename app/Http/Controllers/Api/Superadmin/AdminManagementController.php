<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminManagementController extends Controller
{
    // Manajemen Akun Admin (fitur C.1): tambah, nonaktifkan, reset password
    public function index()
    {
        $admins = User::role('admin')->with('outlet')->latest()->get();
        return response()->json(['admins' => $admins]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        // Password sementara dibuat otomatis, admin wajib ganti saat login pertama
        $tempPassword = Str::random(10);

        $admin = User::create([
            ...$data,
            'password' => Hash::make($tempPassword),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // TODO: kirim password sementara via WhatsApp/Email ke admin baru

        return response()->json(['admin' => $admin, 'temporary_password' => $tempPassword], 201);
    }

    public function toggleActive(User $admin)
    {
        abort_unless($admin->hasRole('admin'), 404);
        $admin->update(['is_active' => !$admin->is_active]);
        return response()->json(['admin' => $admin]);
    }

    // Reset password admin jika lupa/indikasi penyalahgunaan
    public function resetPassword(User $admin)
    {
        abort_unless($admin->hasRole('admin'), 404);
        $tempPassword = Str::random(10);
        $admin->update(['password' => Hash::make($tempPassword)]);

        return response()->json(['message' => 'Password direset.', 'temporary_password' => $tempPassword]);
    }

    public function destroy(User $admin)
    {
        abort_unless($admin->hasRole('admin'), 404);
        $admin->delete();
        return response()->json(['message' => 'Akun admin dihapus.']);
    }
}
