<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    // Log Aktivitas Sistem (fitur C.10): notifikasi gagal terkirim
    // Error log teknis (Laravel log file) sebaiknya diakses langsung lewat storage/logs/laravel.log
    public function notificationLogs(Request $request)
    {
        $logs = NotificationLog::when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(30);

        return response()->json($logs);
    }

    // Tampilkan baris terakhir dari storage/logs/laravel.log (error log & aktivitas cron/sistem)
    public function errorLogs()
    {
        $path = storage_path('logs/laravel.log');

        if (!file_exists($path)) {
            return response()->json(['lines' => []]);
        }

        // Ambil 200 baris terakhir saja agar tidak membebani response
        $lines = collect(file($path))->reverse()->take(200)->reverse()->values();

        return response()->json(['lines' => $lines]);
    }
}
