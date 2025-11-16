<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;
use App\Models\User;

class EventNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $student;

    public function __construct(Event $event, User $student)
    {
        $this->event = $event;
        $this->student = $student;
    }

    public function build()
    {
        return $this->subject('New Event Notification')
            ->view('Mails.event_notification');
    }
}
