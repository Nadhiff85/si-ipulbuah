<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use Illuminate\Http\Request;

// Endpoint publik (tanpa auth) - dipanggil server Midtrans, bukan pengguna.
// Didaftarkan sebagai "Payment Notification URL" di dashboard Midtrans.
class MidtransWebhookController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    public function handle(Request $request)
    {
        $this->midtrans->handleNotification($request->all());

        return response()->json(['message' => 'OK']);
    }
}
