<?php

use Intervention\Image\Drivers\Gd\Driver;

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Sejak Intervention Image v3, kolom ini harus diisi nama class driver,
    | bukan string biasa seperti "gd" di versi 2. Dua pilihan bawaan:
    |
    | - \Intervention\Image\Drivers\Gd\Driver::class      (default, dipakai di sini)
    | - \Intervention\Image\Drivers\Imagick\Driver::class (butuh ekstensi imagick aktif)
    |
    */

    'driver' => Driver::class,

];