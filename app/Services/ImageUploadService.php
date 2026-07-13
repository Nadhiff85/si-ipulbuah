<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Bungkus semua pemakaian Intervention Image di satu tempat.
 *
 * Kenapa dibuat begini: paket intervention/image sempat ganti API secara
 * "breaking" antar versi major (method read() di v2/v3 diganti jadi
 * decode() di v4), dan facade Laravel-nya juga bergantung pada paket
 * tambahan yang gampang beda versi antara satu instalasi dengan yang lain.
 *
 * Supaya tidak terus-terusan error tiap composer install menarik versi
 * yang beda, service ini deteksi sendiri method mana yang tersedia di
 * versi yang benar-benar terpasang, lalu pakai yang cocok. Controller
 * tidak perlu tahu-menahu soal versi package, cukup panggil saveAsWebp().
 */
class ImageUploadService
{
    private ImageManager $manager;

    public function __construct()
    {
        // Driver di-set eksplisit ke class GD, bukan lewat config('image.driver'),
        // supaya tidak bergantung pada file config/service provider paket
        // intervention/image-laravel yang versinya sering tidak sinkron.
        $this->manager = new ImageManager(Driver::class);
    }

    /**
     * Resize (scale down) + konversi ke WebP + simpan ke storage lokal.
     *
     * @param  UploadedFile $file       File hasil $request->file('...')
     * @param  string       $relativePath  Path relatif di dalam storage/app/public, contoh: "products/xxx.webp"
     * @param  int          $maxWidth   Lebar maksimum hasil resize
     * @param  int          $quality    Kualitas kompresi WebP (0-100)
     * @return string       Path publik yang siap disimpan ke kolom database, contoh: "/storage/products/xxx.webp"
     */
    public function saveAsWebp(UploadedFile $file, string $relativePath, int $maxWidth, int $quality = 80): string
    {
        $image = method_exists($this->manager, 'read')
            ? $this->manager->read($file)      // Intervention Image v2 / v3
            : $this->manager->decode($file);   // Intervention Image v4+

        $fullPath = storage_path('app/public/' . $relativePath);
        $directory = dirname($fullPath);

        // Laravel tidak otomatis bikin folder tujuan kalau belum ada.
        // Bikin dulu di sini supaya subfolder baru (products, categories,
        // atau apapun nanti) tidak pernah bikin error "directory does not exist".
        if (!is_dir($directory)) {
            mkdir($directory, 0755, recursive: true);
        }

        // Tidak pakai toWebp() karena method itu sudah dihapus di v4.
        // save() otomatis mendeteksi format dari ekstensi path (.webp),
        // jadi cara ini konsisten jalan baik di v3 maupun v4.
        $image->scaleDown(width: $maxWidth)
            ->save($fullPath, quality: $quality);

        return '/storage/' . $relativePath;
    }
}