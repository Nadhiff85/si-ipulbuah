<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    // Riwayat pesanan pelanggan (fitur A.9 - Pelacakan Pesanan)
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items', 'payment', 'deliverySlot'])
            ->latest()->paginate(10);

        return response()->json($orders);
    }

    // Detail pesanan + timeline status
    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->load(['items.product.images', 'payment', 'deliverySlot', 'address.deliveryRegion']);

        return response()->json([
            'order' => $order,
            'timeline' => $order->status_timeline,
        ]);
    }

    // Minta Snap Token Midtrans untuk pembayaran QRIS otomatis (dipanggil dari
    // halaman Detail Pesanan saat metode bayar = qris & masih menunggu bayar)
    public function getSnapToken(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->loadMissing('payment', 'user');

        abort_if(!$order->payment || $order->payment->method !== 'qris', 422, 'Pesanan ini tidak menggunakan metode QRIS.');
        abort_if($order->payment->status !== 'pending', 422, 'Pembayaran untuk pesanan ini sudah tidak berstatus menunggu.');

        return response()->json(['snap_token' => $this->midtrans->createSnapToken($order)]);
    }

    // Upload bukti transfer bank (fitur A.7)
    public function uploadPaymentProof(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $request->validate(['proof_image' => 'required|image|max:2048']);

        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        $order->payment()->update([
            'proof_image' => $path,
            'status' => 'pending', // menunggu konfirmasi manual admin
        ]);

        return response()->json(['message' => 'Bukti transfer berhasil diunggah, menunggu konfirmasi admin.']);
    }
}
