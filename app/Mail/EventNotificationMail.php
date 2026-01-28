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

class EventNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $event;
    public $student;
    public $isUpdate;
    public $oldEvent;
    public $isCancelled;

    public function __construct(Event $event, User $student, bool $isUpdate = false, $oldEvent = null, bool $isCancelled = false)
    {
        // ✅ THIS is the fix (no property conflict)
        $this->afterCommit();
        $this->event = $event;
        $this->student = $student;
        $this->isUpdate = $isUpdate;
        $this->oldEvent = $oldEvent;
        $this->isCancelled = $isCancelled;
    }

    public function build()
    {
        $subject = 'Event Notification';

        if ($this->isCancelled) {
            $subject = 'Event Cancelled';
        } elseif ($this->isUpdate) {
            $subject = 'Event Updated';
        }

        return $this->subject($subject)
                    ->view('Mails.event_notification');
    }
}
