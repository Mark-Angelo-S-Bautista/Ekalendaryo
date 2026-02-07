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

        // Prepare target departments (for OFFICES events)
        $targetDepartments = [];
        if (strtolower((string) $event->department) === 'offices') {
            $targetDepartments = $event->target_department;
            if (is_string($targetDepartments)) {
                $targetDepartments = json_decode($targetDepartments, true) ?? [];
            }
            if (!is_array($targetDepartments)) {
                $targetDepartments = [];
            }
        }

        // Prepare target sections
        $targetSections = $event->target_sections;
        if (is_string($targetSections)) {
            $targetSections = json_decode($targetSections, true) ?? [];
        }
        if (!is_array($targetSections)) {
            $targetSections = [];
        }

        // Prepare target faculty names
        $targetFacultyNames = [];
        $targetFacultyIds = $event->target_faculty;
        if (is_string($targetFacultyIds)) {
            $targetFacultyIds = json_decode($targetFacultyIds, true) ?? [];
        }
        if (is_array($targetFacultyIds) && count($targetFacultyIds) > 0) {
            $targetFacultyIds = array_map('intval', $targetFacultyIds);
            $targetFacultyNames = User::whereIn('id', $targetFacultyIds)->pluck('name')->toArray();
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
            'targetDepartments' => $targetDepartments,
            'targetSections' => $targetSections,
            'targetFacultyNames' => $targetFacultyNames,
            'isCancelled' => false,
            'isUpdate' => false,
            'oldEvent' => null,
        ]);
    }
}