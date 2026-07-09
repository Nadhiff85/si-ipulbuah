<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Models\ProductView;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Audit Trail Pelanggan (fitur C.5): login/logout, riwayat browsing, keranjang/wishlist,
     * perubahan profil, pesanan & pembayaran, ulasan, notifikasi - untuk keamanan & kepatuhan UU PDP.
     */
    public function customerTrail(Request $request, User $customer)
    {
        abort_unless($customer->hasRole('pelanggan'), 404);

        return response()->json([
            'login_logs' => LoginLog::where('user_id', $customer->id)->latest('login_at')->limit(50)->get(),
            'product_views' => ProductView::where('user_id', $customer->id)->with('product:id,name')->latest('viewed_at')->limit(50)->get(),
            'activities' => AuditLog::where('user_id', $customer->id)->latest()->limit(100)->get(),
            'orders' => $customer->orders()->with('payment')->latest()->get(),
            'reviews' => $customer->reviews()->latest()->get(),
        ]);
    }

    // Daftar pelanggan untuk dipilih (dropdown pencarian sebelum lihat detail trail)
    public function customerList(Request $request)
    {
        $customers = User::role('pelanggan')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->limit(20)->get(['id', 'name', 'email']);

        return response()->json(['customers' => $customers]);
    }

    /**
     * Audit Trail Admin (fitur C.6): siapa mengubah apa, kapan, dari IP mana.
     */
    public function adminTrail(Request $request)
    {
        $logs = AuditLog::whereIn('role', ['admin', 'superadmin'])
            ->with('user:id,name')
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->latest()->paginate(25);

        return response()->json($logs);
    }
}
