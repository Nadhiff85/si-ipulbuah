<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    // Riwayat notifikasi terkirim
    public function index(Request $request)
    {
        $logs = NotificationLog::with('user:id,name')
            ->when($request->channel, fn ($q) => $q->where('channel', $request->channel))
            ->latest()->paginate(20);

        return response()->json($logs);
    }

    // Notifikasi Manual & Broadcast (fitur B.20): kirim ke 1 pelanggan tertentu atau semua pelanggan
    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required|in:single,broadcast',
            'user_id' => 'required_if:target,single|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $userIds = $request->target === 'broadcast'
            ? User::role('pelanggan')->pluck('id')->toArray()
            : [$request->user_id];

        $this->notifications->broadcast($userIds, $request->message);

        return response()->json(['message' => 'Notifikasi berhasil dikirim ke ' . count($userIds) . ' pelanggan.']);
    }
}
