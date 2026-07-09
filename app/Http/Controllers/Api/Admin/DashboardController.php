<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Dashboard Analitik (fitur B.1): ringkasan harian + grafik tren
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $outletId = $request->user()->outlet_id; // null untuk superadmin (semua outlet)

        $baseOrders = Order::query()->when($outletId, fn ($q) => $q->where('outlet_id', $outletId));

        $totalPenjualan = (clone $baseOrders)->where('status', 'selesai')->sum('total');
        $totalPesanan = (clone $baseOrders)->count();
        $pesananDiproses = (clone $baseOrders)->where('status', 'diproses')->count();
        $stokHampirHabis = Product::whereColumn('stock', '<=', 'min_stock_alert')->count();

        // Grafik tren penjualan 7 hari terakhir
        $grafik = (clone $baseOrders)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as tanggal, SUM(total) as total')
            ->groupBy('tanggal')->orderBy('tanggal')->get();

        // Penjualan berdasarkan metode pembayaran
        $metodePembayaran = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->when($outletId, fn ($q) => $q->where('orders.outlet_id', $outletId))
            ->selectRaw('payments.method, SUM(payments.amount) as total')
            ->groupBy('payments.method')->get();

        return response()->json([
            'total_penjualan' => $totalPenjualan,
            'total_pesanan' => $totalPesanan,
            'pesanan_diproses' => $pesananDiproses,
            'stok_hampir_habis' => $stokHampirHabis,
            'grafik_penjualan' => $grafik,
            'metode_pembayaran' => $metodePembayaran,
        ]);
    }
}
