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
| menabrak /api/*, /storage/*, DAN file statis (gambar/css/js/font)
| di folder public/ (mis. /images/marquee/jeruk.jpg, /logo.png, /build/*).
| Sebelumnya bug: file statis ikut "ditangkap" catch-all ini dan malah
| dikembalikan sebagai halaman HTML kosong, bukan file gambar aslinya.
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|storage|images|build|favicon\.ico)(?!.*\.[a-zA-Z0-9]{1,6}$).*$');
