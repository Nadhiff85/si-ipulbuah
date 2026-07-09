<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SI-IPULBUAH
|--------------------------------------------------------------------------
| Karena frontend adalah Vue 3 SPA (client-side routing via vue-router),
| SEMUA route non-API diarahkan ke satu view yang sama (app.blade.php).
| Vue Router yang kemudian menentukan halaman mana yang ditampilkan
| berdasarkan URL (mis. /katalog, /admin, /superadmin, dst).
|
| PENTING: route ini harus didaftarkan PALING TERAKHIR agar tidak
| menabrak /api/* (sudah ditangani terpisah oleh routes/api.php) dan
| /storage/* (file foto produk, QRIS, dll).
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|storage).*$');
