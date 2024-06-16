<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class InfoResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $data_user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data_user)
    {
        $this->data_user = $data_user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $timestamp = $this->data_user->updated_at;
        Carbon::setLocale('ID');
        return $this->subject('Kata Sandi Anda Telah Diganti')
            ->view('info_reset_password')
            ->with([
                'name' => $this->data_user->user_name,
                'date' => Carbon::parse($timestamp)->translatedFormat('d F Y, \J\a\m H.i')
            ]);
    }
}
