<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    // Manajemen Pesanan (fitur B.5): pantau, update status, cetak invoice
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items', 'payment'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->fulfillment_type, fn ($q) => $q->where('fulfillment_type', $request->fulfillment_type))
            ->latest()->paginate(15);

        return response()->json($orders);
    }

    public function show(Order $order)
    {
        return response()->json(['order' => $order->load(['user', 'items.product', 'payment', 'address', 'deliverySlot'])]);
    }

    // Update status pesanan sepanjang alur: menunggu_bayar -> dikonfirmasi -> diproses -> dikirim_siap_ambil -> selesai
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:dikonfirmasi,diproses,dikirim_siap_ambil,selesai,dibatalkan',
        ]);

        $timestampField = [
            'dikonfirmasi' => 'confirmed_at',
            'diproses' => 'processed_at',
            'dikirim_siap_ambil' => 'shipped_at',
            'selesai' => 'completed_at',
        ][$request->status] ?? null;

        $order->update(array_filter([
            'status' => $request->status,
            $timestampField => $timestampField ? now() : null,
        ]));

        // Kirim notifikasi WhatsApp/Email sesuai status baru (fitur A.16/B.20 - template 3-6)
        $templateMap = [
            'diproses' => 'order_processing',
            'dikirim_siap_ambil' => 'order_shipped_or_ready',
            'selesai' => 'order_completed',
        ];

        if (isset($templateMap[$request->status])) {
            $this->notifications->sendOrderNotification($order->fresh(), $templateMap[$request->status]);

            // Setelah selesai, kirim juga undangan memberi ulasan (template 7) beberapa saat kemudian
            if ($request->status === 'selesai') {
                $this->notifications->sendOrderNotification($order->fresh(), 'review_invitation');
            }
        }

        return response()->json(['order' => $order]);
    }

    // Cetak invoice PDF (fitur B.5)
    public function printInvoice(Order $order)
    {
        $order->load(['user', 'items', 'payment', 'address']);
        $pdf = Pdf::loadView('pdf.invoice', ['order' => $order]);
        return $pdf->download("invoice-{$order->order_number}.pdf");
    }
}
