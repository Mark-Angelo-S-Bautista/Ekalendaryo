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
    public $isUpdate;
    public $oldEvent; // old event data

    public function __construct(Event $event, User $student, bool $isUpdate = false, $oldEvent = null)
    {
        $this->event = $event;
        $this->student = $student;
        $this->isUpdate = $isUpdate;
        $this->oldEvent = $oldEvent;
    }

    public function build()
    {
        $subject = $this->isUpdate ? 'Event Updated Notification' : 'New Event Notification';
        return $this->subject($subject)
                    ->view('Mails.event_notification');
    }
}
