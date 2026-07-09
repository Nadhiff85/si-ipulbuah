<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal otomatis (fitur B.19 Peringatan Dini Kesegaran & C.9 Auto-nonaktifkan produk kadaluarsa)
// Jalankan setiap hari jam 07:00 pagi, sebelum toko buka
Schedule::command('products:check-freshness')->dailyAt('07:00');

// Backup database otomatis terjadwal (fitur C.8) - setiap hari jam 02:00 dini hari
Schedule::command('app:backup-database --type=auto')->dailyAt('02:00');
