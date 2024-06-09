<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data_user;
    public $verification_token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data_user, $verification_token)
    {
        $this->data_user = $data_user;
        $this->verification_token = $verification_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Email Verification')
            ->view('verification')
            ->with([
                'name' => $this->data_user->user_name,
                'verificationLink' => $this->verificationLink(),
            ]);
    }

    /**
     * Get the verification link for the user.
     *
     * @return string
     */
    protected function verificationLink()
    {
        $verificationToken = $this->verification_token;
        return url("/verify-email/{$verificationToken}");
    }
}
