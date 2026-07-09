<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class ReportController extends Controller
{
    // Laporan Penjualan (fitur B.12): filter harian/mingguan/bulanan/kustom
    public function sales(Request $request)
    {
        $query = Order::where('status', 'selesai')
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('created_at', '<=', $request->to));

        return response()->json([
            'total' => $query->sum('total'),
            'jumlah_pesanan' => $query->count(),
            'tren' => (clone $query)->selectRaw('DATE(created_at) as tanggal, SUM(total) as total')
                ->groupBy('tanggal')->orderBy('tanggal')->get(),
        ]);
    }

    // Laporan Produk (fitur B.13): ranking terlaris, perbandingan lokal vs impor
    public function products()
    {
        $terlaris = Product::orderByDesc('sold_count')->limit(10)->get(['id', 'name', 'sold_count', 'origin_type']);
        $perbandingan = Product::selectRaw('origin_type, SUM(sold_count) as total_terjual')
            ->groupBy('origin_type')->get();

        return response()->json(['terlaris' => $terlaris, 'perbandingan_asal' => $perbandingan]);
    }

    // Laporan Stok (fitur B.14): stok saat ini + riwayat perubahan (siapa, kapan, alasan)
    public function stock()
    {
        $products = Product::select('id', 'name', 'stock', 'min_stock_alert')
            ->orderBy('stock')->get();

        $recentChanges = \App\Models\StockLog::with(['product:id,name', 'user:id,name'])
            ->latest()->limit(30)->get();

        return response()->json(['products' => $products, 'recent_changes' => $recentChanges]);
    }

    // Laporan Pengiriman (fitur B.15): delivery vs pickup, per kecamatan
    public function shipping()
    {
        $byType = Order::selectRaw('fulfillment_type, COUNT(*) as jumlah, SUM(shipping_cost) as total_ongkir')
            ->groupBy('fulfillment_type')->get();

        $byRegion = DB::table('orders')
            ->join('delivery_regions', 'delivery_regions.id', '=', 'orders.delivery_region_id')
            ->selectRaw('delivery_regions.district, COUNT(*) as jumlah')
            ->groupBy('delivery_regions.district')->get();

        return response()->json(['per_tipe' => $byType, 'per_kecamatan' => $byRegion]);
    }

    // Laporan Pelanggan (fitur B.16): paling aktif, baru, repeat order
    public function customers()
    {
        $palingAktif = User::role('pelanggan')->withCount('orders')
            ->orderByDesc('orders_count')->limit(10)->get(['id', 'name']);

        $repeatOrder = User::role('pelanggan')->withCount('orders')
            ->having('orders_count', '>', 1)->count();

        return response()->json(['paling_aktif' => $palingAktif, 'jumlah_repeat_order' => $repeatOrder]);
    }

    // Laporan Pembayaran (fitur B.17)
    public function payments()
    {
        $rekap = DB::table('payments')->selectRaw('method, status, COUNT(*) as jumlah, SUM(amount) as total')
            ->groupBy('method', 'status')->get();

        return response()->json(['rekap' => $rekap]);
    }

    // Ekspor laporan penjualan ke Excel (fitur B.12)
    public function exportSalesExcel(Request $request)
    {
        return Excel::download(new SalesReportExport($request->from, $request->to), 'laporan-penjualan.xlsx');
    }

    // Ekspor laporan penjualan ke PDF (fitur B.12)
    public function exportSalesPdf(Request $request)
    {
        $query = Order::where('status', 'selesai')
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('created_at', '<=', $request->to));

        $orders = $query->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sales-report', [
            'orders' => $orders,
            'total' => $orders->sum('total'),
            'from' => $request->from,
            'to' => $request->to,
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }
}
