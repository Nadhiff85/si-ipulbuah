<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $templateType,
        public string $message,
    ) {}

    public function build()
    {
        $subjects = [
            'order_created' => 'Pesanan Anda Telah Diterima',
            'payment_confirmed' => 'Pembayaran Dikonfirmasi',
            'order_processing' => 'Pesanan Sedang Diproses',
            'ready_to_ship' => 'Pesanan Siap Dikirim',
            'order_shipped_or_ready' => 'Pesanan Dikirim/Siap Diambil',
            'order_completed' => 'Pesanan Selesai',
            'review_invitation' => 'Beri Ulasan Pesanan Anda',
        ];

        return $this->subject($subjects[$this->templateType] ?? 'Update Pesanan IPUL BUAH')
            ->view('emails.order-status')
            ->with(['order' => $this->order, 'message' => $this->message]);
    }
}
