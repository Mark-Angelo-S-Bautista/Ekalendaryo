<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EventReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $eventId;
    public $userId;
    public $reminderType;

    public function __construct($eventId, $userId, string $reminderType)
    {
        $this->afterCommit();
        
        // Store only IDs
        $this->eventId = is_object($eventId) ? $eventId->id : $eventId;
        $this->userId = is_object($userId) ? $userId->id : $userId;
        $this->reminderType = $reminderType;
    }

    public function build()
    {
        // Fetch fresh data from database
        $event = Event::find($this->eventId);
        $user = User::find($this->userId);
        
        // Don't send if event is cancelled or doesn't exist
        if (!$event || !$user || $event->status === 'cancelled') {
            Log::info('EventReminderMail: Skipping - Event cancelled or not found', [
                'eventId' => $this->eventId,
            ]);
            return null;
        }

        return $this->subject(
            $this->reminderType === '3-days'
                ? 'Upcoming Event in 3 Days'
                : 'Event Reminder: Happening Tomorrow'
        )
        ->view('Mails.event_notification')
        ->with([
            'event' => $event,
            'user' => $user,
            'student' => $user, // for backward compatibility
            'reminderType' => $this->reminderType,
        ]);
    }
}