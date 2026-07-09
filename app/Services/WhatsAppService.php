<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Kirim pesan WhatsApp via WaBlas API & catat ke notification_logs (fitur A.16/B.20).
     * Dipanggil oleh Notification classes di app/Notifications.
     */
    public function send(string $phone, string $message, string $templateType, ?int $userId = null, bool $isBroadcast = false): bool
    {
        $settings = StoreSetting::first();
        $apiKey = $settings?->whatsapp_api_key;

        $log = NotificationLog::create([
            'user_id' => $userId,
            'channel' => 'whatsapp',
            'template_type' => $templateType,
            'message' => $message,
            'is_broadcast' => $isBroadcast,
            'status' => 'pending',
        ]);

        if (!$apiKey) {
            $log->update(['status' => 'failed']);
            Log::warning('WaBlas API key belum dikonfigurasi. Notifikasi WhatsApp tidak terkirim.');
            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $apiKey])
                ->post(config('services.wablas.base_url', 'https://console.wablas.com') . '/api/send-message', [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            $log->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'sent_at' => now(),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed']);
            Log::error('WaBlas send error: ' . $e->getMessage());
            return false;
        }
    }
}
