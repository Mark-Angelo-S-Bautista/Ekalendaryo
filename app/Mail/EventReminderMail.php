<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;
use App\Models\User;

class EventReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $event;
    public $user;
    public $reminderType;
    public $eventId; // ✅ Store event ID separately

    public function __construct(Event $event, User $user, string $reminderType)
    {
        $this->afterCommit();
        $this->event = $event;
        $this->user = $user;
        $this->reminderType = $reminderType;
        $this->eventId = $event->id; // ✅ Store the ID
    }

    public function build()
    {
        // ✅ Fresh check from database before sending
        $event = Event::find($this->eventId);
        
        if (!$event || $event->status === 'cancelled') {
            // Return null to prevent email from being sent
            return null;
        }

        return $this->subject(
            $this->reminderType === '3-days'
                ? 'Upcoming Event in 3 Days'
                : 'Event Reminder: Happening Tomorrow'
        )
        ->view('emails.event_reminder');
    }
}