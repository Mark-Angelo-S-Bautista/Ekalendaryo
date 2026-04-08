<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function build()
    {
        return $this->subject('Your eKalendaryo Account Credentials')
            ->view('Mails.user_credentials')
            ->with([
                'credentials' => $this->credentials,
            ]);
    }
}
