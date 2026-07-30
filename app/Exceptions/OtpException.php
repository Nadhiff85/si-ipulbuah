<?php

namespace App\Exceptions;

use Exception;

// Exception khusus alur OTP - membawa kode status HTTP yang sesuai
// (422 salah, 410 kedaluwarsa/tidak ditemukan, 429 terlalu banyak
// percobaan/kirim ulang, 503 gagal kirim email) supaya controller
// tinggal meneruskan pesan & status-nya ke response JSON.
class OtpException extends Exception
{
    public function __construct(string $message, public int $status = 422)
    {
        parent::__construct($message);
    }
}
