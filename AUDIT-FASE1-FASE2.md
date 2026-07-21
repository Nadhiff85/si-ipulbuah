# Audit & Rekomendasi — IPUL BUAH
**Untuk:** Lomba Inovasi Daerah Masyarakat Berbasis Digitalisasi Pembayaran 2026 (BRIDA Sulteng x Bank Indonesia)
**Reviewer:** Claude (code review session bersama Khaidir), branch `khai`
**Tanggal:** 21 Juli 2026 — Demo juri: 8-9 Agustus 2026

**Metode:** Audit dilakukan lewat 3 jalur paralel: (1) pembacaan langsung `CheckoutController`, `PaymentController`, `AuthController`, `ReportController`, `invoice.blade.php`, model-model inti, dan pengujian nyata alur registrasi→login→checkout→bayar→struk lewat browser; (2) sub-agent khusus audit keamanan & integritas data; (3) sub-agent khusus audit struktur kode & risiko stabilitas. Setiap temuan disertai file:baris yang bisa diverifikasi langsung.

---

## FASE 1 — AUDIT & KRITIK

### 1. Integrasi Payment/QRIS (bobot 50% — PALING MENENTUKAN)

**[KRITIS] Tidak ada integrasi payment gateway/QRIS yang sesungguhnya.**
`app/Http/Controllers/Api/Admin/StoreSettingsController.php` (upload QRIS) + `app/Http/Controllers/Api/Admin/PaymentController.php@updateStatus` (konfirmasi manual).

Yang ada sekarang: admin **mengunggah satu gambar QRIS statis** (seperti print-out QRIS fisik yang di-foto), lalu setiap pelanggan yang checkout melihat gambar yang **sama persis**. Setelah pelanggan "membayar" (di luar sistem — scan pakai e-wallet mereka sendiri), **admin harus klik manual "Konfirmasi"** di dashboard untuk mengubah status jadi `confirmed`. Tidak ada API call ke penyedia QRIS/PSP manapun (grep untuk "midtrans", "xendit", "doku", "snap" di seluruh `app/` = nol hasil), tidak ada webhook, tidak ada verifikasi otomatis bahwa uang benar-benar masuk.

**Kenapa ini paling kritis:** kriteria "Integrasi Digital Payment" bobotnya 50% — lebih besar dari 3 kriteria lain digabung. Juri kemungkinan besar akan bertanya langsung "ini QRIS-nya beneran connect ke payment provider atau cuma gambar?" — dan jawaban jujurnya sekarang adalah **cuma gambar**. Ini bukan salah desain, ini gap fungsional inti yang perlu keputusan tim segera (lihat Fase 2).

**[PENTING] Diskon/promosi tidak pernah diterapkan ke total.**
`app/Http/Controllers/Api/Customer/CheckoutController.php:71` — `$discount = 0; // TODO: hitung dari tabel promotions aktif`. Ada UI admin lengkap untuk membuat promosi (`admin/PromotionsView.vue`), tapi promosi itu **tidak pernah memengaruhi angka checkout**. Kalau admin bikin promo "diskon 10%" untuk demo, total yang dibayar pelanggan tetap penuh — potensi salah data nominal di depan juri (poin uji juri #6: "Data tersimpan akurat, tidak ada kesalahan nominal").

**[KRITIS] Tidak ada pencatatan PPN/PPh sama sekali.**
Grep menyeluruh untuk "pajak", "ppn", "pph", "tax" di `app/` dan `resources/js/` = nol hasil relevan. Model `Order`, migrasi `orders`, `ReportController`, dan `invoice.blade.php` semuanya tidak punya field/kalkulasi pajak apa pun.

Ini eksplisit disebut di poin uji juri #5: **"Laporan transaksi bisa dihasilkan (termasuk pencatatan PPN/PPh)"**. Kalau juri minta lihat laporan dan tanya "PPN-nya berapa?", saat ini tidak ada jawaban di sistem sama sekali.

**[Sudah diperbaiki di sesi ini]** Bug frontend salah hitung total (`Rp 2.250.010.000` bukan `Rp 32.500`, akibat penjumlahan string+angka JavaScript) — sudah saya perbaiki di `CheckoutView.vue`. Tapi ini juga sinyal pola: field desimal (`shipping_cost`, `subtotal`, `total`, dll) di model Laravel **tidak di-`$casts`** sebagai `decimal`/`float` (`app/Models/Order.php:16` hanya cast `scheduled_date`), jadi API selalu mengirim angka-angka ini sebagai **string** ke frontend. Kalau ada kode Vue lain yang menjumlahkan nilai ini tanpa `Number()`, bug yang sama bisa muncul lagi di tempat lain.

### 2. Alur Fitur Inti

| Fitur | Status | Catatan |
|---|---|---|
| Registrasi | **Jalan baik** | Validasi lengkap, password di-hash, role dipaksa `pelanggan` di server (tidak bisa dieskalasi dari frontend) — teruji langsung lewat browser di sesi ini |
| Login | **Jalan baik**, ada proteksi brute-force | Kunci akun otomatis setelah 5x gagal dalam 15 menit (`AuthController.php:69-84`) — implementasi lebih baik dari rata-rata proyek mahasiswa |
| Checkout | **Jalan, tapi rawan** | Lihat temuan race condition di §5 dan diskon di atas |
| Riwayat transaksi | **Jalan baik** | `OrdersView.vue`/`OrderDetailView.vue` — sudah saya redesain, teruji nyata |
| Laporan | **Jalan untuk data non-pajak** | 6 jenis laporan (penjualan, produk, stok, pengiriman, pelanggan, pembayaran) + export Excel/PDF — cukup lengkap, TAPI lihat gap PPN/PPh di atas |
| Struk/bukti transaksi | **Ada, tapi terlihat kurang matang** | `resources/views/pdf/invoice.blade.php` — tabel polos, masih pakai warna hijau lama (`#2E7D32`) dari sebelum saya ganti palet, tidak ada logo, tidak ada breakdown pajak |

### 3. Struktur Kode

**[PENTING] `CheckoutController@store` adalah "fat controller"** (`app/Http/Controllers/Api/Customer/CheckoutController.php`, 148 baris) — validasi, cek kuota slot, aturan minimum order, generate nomor invoice, kurangi stok, buat payment, buat booking, kosongkan cart, kirim notifikasi — semua dalam satu method. Bukan bug, tapi menyulitkan testing/debugging saat ada masalah checkout mendadak sebelum demo.

**[OPSIONAL, kabar baik]** Eager loading (`->with()`) dipakai konsisten di controller admin — tidak ditemukan risiko N+1 query yang signifikan. Tidak ada `dd()`/`dump()`/`console.log` tertinggal. Route guard di `resources/js/router/index.js` sudah benar (pengguna belum login tidak bisa akses `/checkout` langsung).

### 4. UI/UX

Sisi **customer sudah saya rombak** di sesi ini (glassmorphism "Liquid Glass" + Heroicons pengganti emoji + palet terracotta-hijau, 18 halaman, teruji end-to-end di browser). Yang **belum tersentuh dan berpotensi menurunkan kesan "matang" di depan juri**:

- **[PENTING] Dashboard admin & superadmin (28 halaman)** — masih pakai gaya lama sepenuhnya (kartu putih polos, kemungkinan masih ada emoji sebagai ikon — belum diaudit detail karena di luar cakupan kerja sebelumnya). Juri kemungkinan besar akan melihat sisi admin juga saat demo alur "verifikasi pembayaran" dan "lihat laporan".
- **[PENTING] Struk PDF (`invoice.blade.php`)** — polos, warna belum konsisten dengan brand baru, tidak ada breakdown pajak (terkait §1).
- **[OPSIONAL]** Katalog produk kosong di instalasi baru (lihat §5) — bukan masalah UI langsung, tapi juri yang mencoba fresh-install akan melihat toko kosong.

### 5. Keamanan

**[KRITIS] Admin biasa bisa mengintip/menonaktifkan akun superadmin lain (IDOR/broken access control).**
`app/Http/Controllers/Api/Admin/CustomerController.php` method `show()` dan `toggleActive()` — route hanya dijaga `role:admin,superadmin` (`routes/api.php:143-145`) tapi controller **tidak mengecek** bahwa `{id}` yang diakses benar-benar berrole `pelanggan`. Bandingkan dengan `AdminManagementController.php` yang benar melakukan `abort_unless($admin->hasRole('admin'), 404)`. Akibatnya: admin biasa bisa `PATCH /admin/customers/{id-superadmin}/toggle-active` untuk menonaktifkan akun superadmin, atau `GET` untuk mengintip datanya. Ini murni bug logika akses — **REKOMENDASI UNTUK DIDISKUSIKAN DENGAN TIM**, perlu Nadhif.

**[KRITIS] Pelanggan bisa memindahkan alamat miliknya ke akun user lain (mass assignment).**
`app/Http/Controllers/Api/Customer/AddressController.php` — method update alamat memakai `$address->update($request->all())`, dan `user_id` termasuk `$fillable` di `Address` model. Pelanggan bisa kirim `user_id` custom di body request PATCH alamatnya sendiri dan mengubah kepemilikannya. **REKOMENDASI UNTUK DIDISKUSIKAN DENGAN TIM.**

**[KRITIS, cek konfigurasi]** `.env.example` menyetel `APP_DEBUG=true`. Kalau `.env` di server demo copy-paste dari situ tanpa diubah, error apa pun saat demo akan menampilkan stack trace lengkap (path file server, query SQL) ke layar — buruk sekali di depan juri. **Ini murni cek konfigurasi, bukan kode** — tolong pastikan `.env` yang dipakai saat demo punya `APP_DEBUG=false`.

**[PENTING] `cost_price` (harga modal) produk bocor ke publik.**
`Product` model tidak punya `$hidden`, dan endpoint katalog publik (`Customer/ProductController.php`) mengembalikan seluruh model termasuk `cost_price` — siapa pun yang buka Network tab browser bisa lihat margin keuntungan toko per produk. **REKOMENDASI UNTUK DIDISKUSIKAN DENGAN TIM** (touch model/controller Nadhif), tapi solusinya simpel.

**[PENTING] Kredensial WhatsApp API & SMTP bocor ke role admin biasa.**
`Admin/StoreSettingsController.php` mengembalikan `whatsapp_api_key` dan config SMTP mentah ke siapa pun berrole `admin` (seharusnya superadmin-only atau disamarkan).

**[OPSIONAL]** CORS masih pakai default framework (`allowed_origins: ['*']`) — belum jadi masalah karena auth pakai Bearer token bukan cookie, tapi sebaiknya dikunci ke domain asli sebelum demo.

### 6. Risiko Saat Demo Langsung

| Risiko | Tingkat | Penjelasan |
|---|---|---|
| Nomor invoice bentrok (race condition) | **KRITIS** | `CheckoutController.php:75` menghitung nomor invoice dari `COUNT()`, tanpa row-lock. Kalau 2 laptop juri checkout bersamaan (masuk akal saat demo interaktif), bisa terjadi duplikat `order_number` yang melanggar unique constraint DB → **transaksi gagal dengan error 500** persis saat didemokan |
| Katalog kosong di instalasi baru | **KRITIS** | Tidak ada seeder produk (`DatabaseSeeder.php` hanya isi role/kategori/wilayah/toko, bukan produk) — sudah saya siasati sementara dengan 2 produk manual untuk testing, tapi ini bukan solusi permanen |
| Nol automated test | **PENTING** | `tests/Feature/ExampleTest.php` masih boilerplate default Laravel. Kalau ada perbaikan mendadak H-1 sebelum demo, tidak ada jaring pengaman untuk tahu sesuatu jadi rusak |
| Tidak ada penanganan error API yang konsisten di frontend | **PENTING** | `api.js` cuma intercept HTTP 401; error 422/500 lain harus ditangani manual per komponen — kalau ada satu halaman yang lupa `try/catch`, bisa gagal senyap tanpa pesan ke pengguna saat demo |

---

## FASE 2 — REKOMENDASI

*(Diurutkan berdasarkan dampak ke skor penilaian, bukan berdasarkan urutan ditemukan)*

### Prioritas 1 — Integrasi Payment (dampak ke 50% bobot)

**Ini keputusan tim, bukan hal yang bisa saya putuskan/eksekusi sendiri.** Tiga opsi realistis mengingat aplikasi harus selesai akhir Juli:

| Opsi | Kompleksitas | Trade-off |
|---|---|---|
| **A. Integrasi PSP sungguhan** (Midtrans/Xendit Snap QRIS sandbox) | **SULIT** — butuh akun sandbox, webhook, verifikasi signature, testing ujung-ke-ujung | Paling kuat untuk skor 50%, tapi risiko waktu tinggi kalau baru mulai sekarang; kalau gagal H-3 bisa lebih buruk daripada tidak coba sama sekali |
| **B. Simulasi yang transparan & terdokumentasi** (status sekarang, tapi didokumentasikan eksplisit sebagai "MVP tahap 1 — arsitektur siap untuk integrasi PSP, saat ini pakai verifikasi manual admin karena keterbatasan waktu develop") | **MUDAH** | Jujur ke juri, mengurangi risiko dianggap "bohong", tapi kemungkinan tidak dapat nilai penuh di kriteria 50% ini |
| **C. Integrasi QRIS statis dari Bank Indonesia / bank mitra lokal (bukan API, tapi QRIS resmi terverifikasi merchant)** | **SEDANG** | Kalau toko sudah punya QRIS resmi merchant (bukan QRIS pribadi), setidaknya bisa ditunjukkan sebagai QRIS sah — masih manual konfirmasi, tapi lebih kredibel daripada QRIS acak |

**Rekomendasi saya:** diskusikan opsi A vs B dengan Nadhif SEGERA (bukan mendekati deadline) — ini keputusan arsitektur besar yang menentukan berapa banyak waktu backend tersisa untuk hal lain.

### Prioritas 2 — PPN/PPh di Laporan

**Kompleksitas: SEDANG.** Tidak perlu terintegrasi ke kantor pajak sungguhan — cukup:
1. Tambah field `tax_rate`/`tax_amount` di migrasi `orders` (atau hitung on-the-fly dari `subtotal * 11%` sesuai tarif PPN saat ini) — **backend, Nadhif**
2. Tampilkan baris "PPN (11%)" di `invoice.blade.php` dan di `ReportController@sales`/`SalesReportExport` — **backend untuk logika, saya bisa bantu styling template setelah field-nya ada**

Ini quick-win yang relatif murah dibanding integrasi payment, dan langsung menjawab poin eksplisit di rubrik juri.

### Prioritas 3 — Perbaikan Keamanan KRITIS (dampak ke 20% "kelayakan & keamanan")

Semua ini **REKOMENDASI UNTUK DIDISKUSIKAN DENGAN TIM** karena menyentuh controller/model milik Nadhif:

1. `CustomerController@show/toggleActive` — tambah `abort_unless($customer->hasRole('pelanggan'), 404)` — **MUDAH**, 1 baris per method
2. `AddressController@update` — ganti `$request->all()` dengan whitelist field eksplisit (`only(['label','recipient_name','phone','full_address','delivery_region_id'])`) — **MUDAH**
3. Cek `.env` produksi/demo: pastikan `APP_DEBUG=false` — **MUDAH**, bukan kode
4. `cost_price` di Product — sembunyikan dari response publik (tambah `$hidden` atau pakai API Resource) — **MUDAH-SEDANG**
5. `StoreSettingsController` — batasi field sensitif (`whatsapp_api_key`, SMTP) hanya untuk superadmin — **MUDAH**

### Prioritas 4 — Stabilitas untuk Hari-H

1. **Race condition nomor invoice** — ganti generator nomor invoice pakai `lockForUpdate()` di transaction, atau pakai UUID/ULID + nomor urut terpisah yang tidak rawan collision — **SEDANG**, backend (Nadhif)
2. **Seeder produk permanen** — buat `ProductSeeder` dengan minimal 10-15 produk realistis supaya `php artisan migrate --seed` selalu menghasilkan toko yang siap didemokan — **MUDAH**, dan ini saya bisa kerjakan sendiri kalau disetujui (murni data seed, tidak mengubah logika)
3. **Minimal test untuk jalur kritis** (registrasi→login→checkout→bayar) — bukan demi coverage sempurna, tapi demi jaring pengaman kalau ada perbaikan mendadak H-1 — **SEDANG**, idealnya Nadhif yang isi assertion bisnisnya, saya bisa bantu setup struktur test

### Prioritas 5 — UI/UX untuk Area yang Belum Tersentuh

Sisi customer sudah selesai (Liquid Glass, terracotta+hijau, ikon Heroicons). Untuk **dashboard admin/superadmin** dan **struk PDF**, tiga arah:

**A. Lanjutkan Liquid Glass ke admin (konsistensi visual)**
Pakai token warna & sedikit efek kaca di header/kartu ringkasan admin, supaya seluruh app terasa satu produk saat juri berpindah dari sisi customer ke admin.
*Catatan jujur dari saya:* efek blur kaca (`backdrop-filter`) di tabel data padat (daftar pesanan, daftar pembayaran, puluhan baris) **saya TIDAK rekomendasikan** — bukan cuma soal performa, tapi transparansi kartu kaca bikin teks di tabel padat lebih susah dipindai cepat, dan admin/superadmin adalah power-user yang butuh kecepatan baca data, bukan estetika. Saran: kaca hanya di header/kartu statistik, tabel tetap flat solid.

**B. "Hangat UMKM Lokal"** — palet earthy, foto asli produk/toko lebih dominan, storytelling ("langsung dari petani Palu"). Cocok kalau ingin menonjolkan kriteria "Dampak terhadap UMKM" (10%) secara visual, tapi kurang cocok untuk dashboard admin yang butuh efisiensi data.

**C. "Modern Marketplace/Dashboard-Clean"** — flat, kontras tinggi, padat-informasi, ala seller-center Shopee/Tokopedia. Paling cocok secara fungsional untuk admin/superadmin spesifik (bukan customer-facing), karena mengoptimalkan kecepatan kerja staf toko, bukan daya tarik visual.

**Rekomendasi saya:** A (glass di header saja) + C (flat/dense di tabel) digabung — bukan salah satu murni. Ini murni frontend, bisa saya kerjakan sendiri setelah disetujui arahnya.

**Struk PDF** — setelah field PPN ada (Prioritas 2), saya bisa styling ulang `invoice.blade.php` dengan palet baru + logo + breakdown pajak. Murni frontend/template, aman saya kerjakan sendiri.

---

## Ringkasan: Frontend (saya bisa putuskan/kerjakan sendiri) vs Backend (perlu Nadhif)

**Aman saya kerjakan sendiri:**
- Redesign dashboard admin/superadmin (arah A+C)
- Restyling struk PDF (setelah field PPN tersedia)
- Perbaikan penanganan error di frontend (`api.js` interceptor untuk 422/500/419)
- Seeder produk contoh (`ProductSeeder`) — data seed murni, bukan logika bisnis

**Wajib didiskusikan dengan Nadhif dulu:**
- Keputusan arsitektur payment (opsi A/B/C di atas) — **paling mendesak**
- Field & kalkulasi PPN/PPh di model `Order`
- Fix authorization bug di `CustomerController`
- Fix mass-assignment di `AddressController`
- Sembunyikan `cost_price` dan kredensial API dari response
- Fix race condition nomor invoice
- Penambahan test otomatis untuk jalur kritis

---

**STOP — menunggu review dan arahan Khaidir sebelum lanjut ke Fase 3 (eksekusi).**
