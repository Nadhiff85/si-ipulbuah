# IPUL BUAH — SI-IPULBUAH
Sistem Informasi Penjualan Buah Segar Berbasis Web
Stack: **Laravel 13 + Vue 3 (menyatu dalam 1 project, via Vite)** + **MySQL**

## 📦 Struktur Project (mengikuti template resmi Laravel)
```
si-ipulbuah/                  ← root project (hasil composer create-project)
├── app/
│   ├── Models/                → 31 Eloquent model
│   ├── Http/Controllers/Api/  → Auth, Customer, Admin, Superadmin
│   ├── Http/Middleware/       → CheckRole, LogAudit, CheckMaintenanceMode, dll
│   ├── Services/              → WhatsAppService, NotificationService
│   ├── Console/Commands/      → CheckProductFreshness (scheduled job)
│   ├── Mail/                  → OrderStatusMail
│   └── Exports/               → SalesReportExport
├── bootstrap/app.php          → registrasi middleware & routing
├── config/services.php        → konfigurasi WaBlas
├── database/
│   ├── migrations/            → 33 tabel
│   └── seeders/                → Role/Permission, Superadmin, Area & Slot Pengiriman
├── resources/
│   ├── css/app.css            → Tailwind + tema Fresh Orchard
│   ├── js/                    → SELURUH SOURCE VUE ADA DI SINI
│   │   ├── main.js, App.vue
│   │   ├── router/            → peta 3 modul (pelanggan/admin/superadmin)
│   │   ├── stores/            → Pinia (auth, cart, store info)
│   │   ├── services/api.js    → axios client
│   │   ├── layouts/           → CustomerLayout, AdminLayout, SuperadminLayout
│   │   ├── components/        → shared, admin
│   │   └── views/             → customer/, admin/, superadmin/
│   └── views/
│       ├── app.blade.php      → SPA shell (dimuat sekali, sisanya di-render Vue Router)
│       ├── emails/            → template email
│       └── pdf/               → template invoice & laporan
├── routes/
│   ├── web.php                 → fallback SEMUA route ke SPA (app.blade.php)
│   ├── api.php                 → seluruh REST API (/api/*)
│   └── console.php             → jadwal cron (freshness check, backup)
├── public/                     → taruh logo.png Anda di sini
├── package.json                → dependency Vue (digabung dengan punya Laravel)
├── vite.config.js              → laravel-vite-plugin + @vitejs/plugin-vue
├── tailwind.config.js
└── composer.json
```

**Poin penting**: ini **1 project Laravel tunggal**. Vue TIDAK dijalankan sebagai server terpisah (tidak ada `npm run dev` di port 5173 yang berdiri sendiri) — Vite terintegrasi langsung lewat `@vite()` di `app.blade.php`, jadi Anda hanya perlu **1 URL** (`http://localhost:8000`) untuk seluruh aplikasi.

## 🚀 Cara Menjalankan

### 1. Buat project Laravel baru lalu timpa dengan isi paket ini
```bash
composer create-project laravel/laravel si-ipulbuah
cd si-ipulbuah
```
Salin **seluruh isi** paket zip ini ke folder `si-ipulbuah` tadi (timpa file yang bawaan: `app/`, `bootstrap/app.php`, `database/`, `resources/`, `routes/`, `package.json`, `vite.config.js`, `composer.json`).

### 2. Install dependency PHP
```bash
composer install
```

### 3. Install dependency Node/Vue
```bash
npm install
```

### 4. Siapkan .env
```bash
cp .env.example .env
php artisan key:generate
```
Isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sesuai MySQL Anda.

### 5. Publish config package
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

> **Catatan Backup Database**: fitur backup TIDAK memakai package `spatie/laravel-backup` (belum mendukung Laravel 13 saat ini), melainkan command mandiri (`app:backup-database`) yang memanggil `mysqldump` langsung. Pastikan `mysqldump` bisa diakses:
> - **Laragon/XAMPP di Windows**: biasanya sudah otomatis ada di PATH. Jika command backup gagal, tambahkan baris ini di `.env`:
>   ```
>   MYSQLDUMP_PATH="C:\laragon\bin\mysql\mysql-8.x.x-winx64\bin\mysqldump.exe"
>   ```
>   (sesuaikan path dengan versi MySQL Anda)
> - **Linux/Mac**: biasanya `mysqldump` sudah tersedia otomatis via `which mysqldump`

### 6. Buat database & migrasi
```bash
mysql -u root -p -e "CREATE DATABASE ipul_buah"
php artisan migrate
php artisan db:seed
```

### 7. Symbolic link storage (foto produk, QRIS)
```bash
php artisan storage:link
```

### 8. Taruh logo toko
Copy file logo Anda ke `public/logo.png` (dipakai di header, footer, halaman login/register).

### 9. Jalankan (2 mode)

**Mode Development** (2 terminal terpisah, tapi tetap 1 URL akses):
```bash
# Terminal 1 - backend + serve halaman
php artisan serve

# Terminal 2 - compile Vue secara live (hot reload)
npm run dev
```
Buka **http://localhost:8000** (BUKAN port 5173 — itu cuma proses compile Vite di background).

**Mode Produksi** (build sekali jadi, tanpa perlu `npm run dev` terus aktif):
```bash
npm run build
php artisan serve
```

### 10. (Opsional) Jalankan scheduler untuk fitur otomatis
```bash
php artisan schedule:work
```
Ini menjalankan Peringatan Dini Kesegaran & Backup Database otomatis sesuai jadwal.

## 🔑 Akun Default (dari seeder)
| Role | Email | Password |
|---|---|---|
| Superadmin | superadmin@ipulbuah.com | IpulBuah#2026 |
| Admin Toko | admin@ipulbuah.com | AdminToko#2026 |

⚠️ **Wajib ganti password ini setelah deploy!**

## 📍 Status Fitur
Seluruh 55+ fitur blueprint (Modul Pelanggan, Admin Toko, Superadmin) sudah diimplementasikan, termasuk:
- Notifikasi WhatsApp (WaBlas) & Email otomatis (7 template status pesanan)
- QRIS & rekening bank di checkout, ulasan produk, edit profil
- Riwayat perubahan stok, jadwal & kuota pengiriman, notifikasi broadcast
- Peringatan dini kesegaran + auto-nonaktifkan produk kadaluarsa (scheduled job)
- Enforcement keamanan: session timeout, max login attempt, rate limiting, maintenance mode

### Yang masih perlu dilengkapi manual:
- Isi `WABLAS_API_KEY` di `.env` untuk notifikasi WhatsApp benar-benar terkirim
- Isi kredensial SMTP di `.env` untuk email benar-benar terkirim
- Setup cron job Laravel Scheduler di server produksi: `* * * * * php artisan schedule:run`
