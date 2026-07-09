<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
{
    // Backup & Restore Database (fitur C.8) - memakai command mysqldump mandiri
    public function index()
    {
        return response()->json(['backups' => Backup::with('createdBy:id,name')->latest()->get()]);
    }

    // Jalankan backup manual
    public function store(Request $request)
    {
        Artisan::call('app:backup-database', ['--type' => 'manual']);
        $output = Artisan::output();

        $backup = Backup::latest()->first();

        if (!$backup) {
            return response()->json(['message' => 'Backup gagal dijalankan.', 'detail' => $output], 500);
        }

        return response()->json(['backup' => $backup], 201);
    }

    // Restore: import kembali file .sql ke database (dijalankan manual oleh Superadmin lewat terminal
    // demi keamanan - tidak diizinkan lewat HTTP request untuk mencegah penyalahgunaan)
    public function restore(Backup $backup)
    {
        return response()->json([
            'message' => 'Demi keamanan, proses restore database tidak dijalankan otomatis lewat web. '
                . 'Silakan jalankan manual di terminal server: '
                . "mysql -u [user] -p [database] < storage/app/{$backup->file_path}",
        ]);
    }

    public function download(Backup $backup)
    {
        return response()->download(storage_path('app/' . $backup->file_path));
    }
}
