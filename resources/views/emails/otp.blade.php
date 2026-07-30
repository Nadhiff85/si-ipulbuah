<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background:#FFF6EC; padding:24px; margin:0;">
    <div style="max-width:440px;margin:auto;">

        <div style="text-align:center;margin-bottom:20px;">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px;background:#123524;color:#fff;font-weight:800;font-size:15px;">IB</span>
            <p style="margin:8px 0 0;font-weight:800;color:#123524;font-size:16px;letter-spacing:0.3px;">IPUL BUAH</p>
        </div>

        <div style="background:#fff;border-radius:20px;padding:32px 28px;box-shadow:0 2px 10px rgba(0,0,0,0.05);">

            <h2 style="margin:0 0 6px;color:#1F1206;font-size:18px;">
                @if($purpose === 'reset_password')
                    Reset Kata Sandi
                @else
                    Kode Verifikasi Masuk
                @endif
            </h2>
            <p style="margin:0 0 24px;color:#1F1206;opacity:0.6;font-size:14px;line-height:1.6;">
                Halo {{ $user->name }},
                @if($purpose === 'reset_password')
                    kami menerima permintaan untuk mengatur ulang kata sandi akun Anda. Masukkan kode berikut untuk melanjutkan:
                @else
                    gunakan kode berikut untuk menyelesaikan proses masuk ke akun Anda:
                @endif
            </p>

            <div style="background:#FFF6EC;border:1.5px dashed #FF5A36;border-radius:14px;padding:18px;text-align:center;margin-bottom:20px;">
                <span style="font-size:34px;font-weight:800;letter-spacing:10px;color:#123524;font-family:'Courier New',monospace;">{{ $code }}</span>
            </div>

            <p style="margin:0 0 4px;color:#1F1206;opacity:0.55;font-size:12.5px;text-align:center;">
                Kode berlaku selama {{ $expiresMinutes }} menit.
            </p>
            <p style="margin:0;color:#1F1206;opacity:0.55;font-size:12.5px;text-align:center;">
                Jangan bagikan kode ini kepada siapa pun, termasuk pihak yang mengaku dari IPUL BUAH.
            </p>

            <hr style="border:none;border-top:1px solid #F0E6D8;margin:24px 0;">

            <p style="margin:0;color:#1F1206;opacity:0.4;font-size:11.5px;line-height:1.6;">
                Kalau Anda tidak merasa meminta kode ini, abaikan saja email ini - akun Anda tetap aman dan tidak ada perubahan yang terjadi.
            </p>
        </div>

        <p style="text-align:center;color:#1F1206;opacity:0.35;font-size:11px;margin-top:18px;">
            IPUL BUAH &middot; Segar Setiap Hari, Sehat untuk Keluarga.
        </p>
    </div>
</body>
</html>
