<?php

namespace App\Mail;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public string $purpose, // login | reset_password
    ) {}

    public function build()
    {
        $subjects = [
            'login' => 'Kode Verifikasi Masuk - IPUL BUAH',
            'reset_password' => 'Kode Reset Kata Sandi - IPUL BUAH',
        ];

        return $this->subject($subjects[$this->purpose] ?? 'Kode Verifikasi IPUL BUAH')
            ->view('emails.otp')
            ->with([
                'user' => $this->user,
                'code' => $this->code,
                'purpose' => $this->purpose,
                'expiresMinutes' => OtpService::EXPIRES_MINUTES,
            ]);
    }
}
