<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data_user;
    public $otp;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data_user, $otp)
    {
        $this->data_user = $data_user;
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Atur Ulang Kata Sandi')
            ->view('email_reset_password')
            ->with([
                'name' => $this->data_user->user_name,
                'otp' => $this->otp,
            ]);
    }

    /**
     * Get the verification link for the user.
     *
     * @return string
     */
    // protected function otp()
    // {
    //     $otp = $this->otp;
    //     return $otp;
    // }
}
