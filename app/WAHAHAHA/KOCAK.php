otificationLog;
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