<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

/**
 * Bungkus semua pemakaian Midtrans Snap (QRIS otomatis) di satu tempat.
 * Order & Payment tetap dibuat seperti biasa oleh CheckoutController - service
 * ini baru dipanggil saat pelanggan membuka halaman Detail Pesanan (on-demand),
 * supaya proses checkout tidak bergantung pada ketersediaan API pihak ketiga.
 */
class MidtransService
{
    public function __construct(private NotificationService $notifications)
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // Midtrans cuma izinkan alfanumerik + - _ ~ . pada order_id, sedangkan
    // format order_number kita pakai "/" (mis. INV/20260721/0003) - jadi
    // dikonversi khusus untuk dikirim ke Midtrans, order_number asli di
    // database tidak berubah.
    private function toMidtransOrderId(string $orderNumber): string
    {
        return str_replace('/', '-', $orderNumber);
    }

    // Minta Snap Token untuk sebuah order - dipakai frontend menampilkan QRIS.
    public function createSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $this->toMidtransOrderId($order->order_number),
                'gross_amount' => (int) round($order->total),
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
            ],
            // Tidak dibatasi ke satu channel - Snap otomatis menampilkan semua
            // metode yang aktif di akun Midtrans (QRIS, Transfer Bank/VA,
            // GoPay, ShopeePay, dst), jadi pelanggan bebas pilih di popup.
        ];

        return Snap::getSnapToken($params);
    }

    // Verifikasi signature notifikasi dari Midtrans, lalu update status Payment
    // & Order - ini yang menggantikan klik "Konfirmasi" manual admin untuk QRIS.
    public function handleNotification(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        abort_if(!$orderId || !$statusCode || !$grossAmount || !$signatureKey, 400, 'Payload notifikasi tidak lengkap.');

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.config('services.midtrans.server_key'));
        abort_unless(hash_equals($expectedSignature, $signatureKey), 403, 'Signature tidak valid.');

        $order = Order::whereRaw("REPLACE(order_number, '/', '-') = ?", [$orderId])->with('payment')->firstOrFail();
        $payment = $order->payment;
        abort_if(!$payment, 404, 'Payment untuk order ini tidak ditemukan.');

        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (in_array($transactionStatus, ['capture', 'settlement'], true) && ($fraudStatus === null || $fraudStatus === 'accept')) {
            if ($payment->status !== 'confirmed') {
                $payment->update([
                    'qris_reference' => $payload['transaction_id'] ?? $payment->qris_reference,
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
                $order->update(['status' => 'dikonfirmasi', 'confirmed_at' => now()]);
                $this->notifications->sendOrderNotification($order->fresh(), 'payment_confirmed');
            }
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true)) {
            $payment->update([
                'qris_reference' => $payload['transaction_id'] ?? $payment->qris_reference,
                'status' => 'rejected',
            ]);
        }
        // 'pending' - belum discan/belum bayar, tidak ada perubahan status.
    }
}
