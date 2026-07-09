<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    // Manajemen Pembayaran (fitur B.6): verifikasi bukti transfer, filter metode/tanggal
    public function index(Request $request)
    {
        $payments = Payment::with('order.user')
            ->when($request->method, fn ($q) => $q->where('method', $request->method))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date, fn ($q) => $q->whereDate('created_at', $request->date))
            ->latest()->paginate(15);

        return response()->json($payments);
    }

    // Konfirmasi atau tolak bukti transfer manual
    public function updateStatus(Request $request, Payment $payment)
    {
        $request->validate(['status' => 'required|in:confirmed,rejected']);

        $payment->update([
            'status' => $request->status,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        if ($request->status === 'confirmed') {
            $payment->order->update(['status' => 'dikonfirmasi', 'confirmed_at' => now()]);
            $this->notifications->sendOrderNotification($payment->order->fresh(), 'payment_confirmed');
        }

        return response()->json(['payment' => $payment]);
    }
}
