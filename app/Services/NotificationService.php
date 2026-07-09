<?php

namespace App\Services;

use App\Mail\OrderStatusMail;
use App\Models\NotificationLog;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(private WhatsAppService $whatsapp) {}

    /**
     * Kirim notifikasi WA + Email sekaligus untuk 7 template status pesanan (fitur A.16):
     * 1. order_created  2. payment_confirmed  3. order_processing
     * 4. ready_to_ship  5. order_shipped_or_ready  6. order_completed  7. review_invitation
     */
    public function sendOrderNotification(Order $order, string $templateType): void
    {
        $user = $order->user;
        $message = $this->buildMessage($order, $templateType);

        // WhatsApp
        $this->whatsapp->send($user->phone, $message, $templateType, $user->id);

        // Email
        try {
            Mail::to($user->email)->send(new OrderStatusMail($order, $templateType, $message));
            NotificationLog::create([
                'user_id' => $user->id, 'channel' => 'email', 'template_type' => $templateType,
                'message' => $message, 'status' => 'sent', 'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            NotificationLog::create([
                'user_id' => $user->id, 'channel' => 'email', 'template_type' => $templateType,
                'message' => $message, 'status' => 'failed',
            ]);
        }
    }

    // Broadcast manual ke banyak pelanggan sekaligus (fitur B.20)
    public function broadcast(array $userIds, string $message): void
    {
        foreach ($userIds as $userId) {
            $user = \App\Models\User::find($userId);
            if (!$user) continue;

            $this->whatsapp->send($user->phone, $message, 'broadcast_manual', $user->id, isBroadcast: true);

            try {
                Mail::raw($message, function ($mail) use ($user) {
                    $mail->to($user->email)->subject('Informasi dari IPUL BUAH');
                });
                NotificationLog::create([
                    'user_id' => $user->id, 'channel' => 'email', 'template_type' => 'broadcast_manual',
                    'message' => $message, 'status' => 'sent', 'is_broadcast' => true, 'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                NotificationLog::create([
                    'user_id' => $user->id, 'channel' => 'email', 'template_type' => 'broadcast_manual',
                    'message' => $message, 'status' => 'failed', 'is_broadcast' => true,
                ]);
            }
        }
    }

    private function buildMessage(Order $order, string $type): string
    {
        return match ($type) {
            'order_created' => "Halo {$order->user->name}, pesanan {$order->order_number} telah kami terima dan menunggu pembayaran. Total: Rp " . number_format($order->total, 0, ',', '.'),
            'payment_confirmed' => "Pembayaran untuk pesanan {$order->order_number} telah dikonfirmasi. Pesanan Anda akan segera kami proses. Terima kasih! 🍊",
            'order_processing' => "Pesanan {$order->order_number} sedang kami siapkan dengan penuh perhatian. Mohon ditunggu ya!",
            'ready_to_ship' => "Pesanan {$order->order_number} sudah siap dikirim/diambil. Kami akan segera menghubungi Anda.",
            'order_shipped_or_ready' => $order->fulfillment_type === 'delivery'
                ? "Pesanan {$order->order_number} sedang dalam perjalanan menuju Anda. 🚚"
                : "Pesanan {$order->order_number} sudah siap diambil di toko IPUL BUAH. 🏬",
            'order_completed' => "Pesanan {$order->order_number} telah selesai. Terima kasih telah berbelanja di IPUL BUAH! 🍎",
            'review_invitation' => "Bagaimana pengalaman belanja Anda? Yuk beri ulasan untuk pesanan {$order->order_number} di aplikasi IPUL BUAH ⭐",
            default => "Update pesanan {$order->order_number}.",
        };
    }
}
