<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    // Monitoring Kinerja Sistem (fitur C.12): pengguna aktif, storage, kuota notifikasi
    public function index()
    {
        $start = microtime(true);
        $totalUsers = User::count(); // query sederhana untuk mengukur waktu respon riil
        $responseTimeMs = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'waktu_respon_server_ms' => $responseTimeMs,
            'pengguna_aktif_24jam' => User::where('last_login_at', '>=', now()->subDay())->count(),
            'total_pengguna' => $totalUsers,
            'total_admin' => User::role('admin')->count(),
            'notifikasi_terkirim_hari_ini' => NotificationLog::whereDate('created_at', now())->where('status', 'sent')->count(),
            'notifikasi_gagal_hari_ini' => NotificationLog::whereDate('created_at', now())->where('status', 'failed')->count(),
            'storage_terpakai_mb' => round(disk_total_space(storage_path()) > 0 ? (disk_total_space(storage_path()) - disk_free_space(storage_path())) / 1048576 : 0, 2),
        ]);
    }
}
