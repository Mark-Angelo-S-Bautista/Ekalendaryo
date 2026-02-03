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
use Illuminate\Support\Facades\Log;

class EventNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $eventId;
    public $userId;
    public $isUpdate;
    public $oldEventData; // Store as array instead of model
    public $isCancelled;

    public function __construct($eventId, $userId, bool $isUpdate = false, $oldEventData = null, bool $isCancelled = false)
    {
        $this->afterCommit();
        
        // Store only IDs and primitive data
        $this->eventId = is_object($eventId) ? $eventId->id : $eventId;
        $this->userId = is_object($userId) ? $userId->id : $userId;
        $this->isUpdate = $isUpdate;
        $this->isCancelled = $isCancelled;
        
        // Convert oldEvent model to array if it exists
        if ($oldEventData && is_object($oldEventData)) {
            $this->oldEventData = $oldEventData->toArray();
        } else {
            $this->oldEventData = $oldEventData;
        }
    }

    public function build()
    {
        // Fetch fresh data from database
        $event = Event::find($this->eventId);
        $student = User::find($this->userId);

        // Handle case where event or user no longer exists
        if (!$event || !$student) {
            Log::warning('EventNotificationMail: Event or User not found', [
                'eventId' => $this->eventId,
                'userId' => $this->userId,
            ]);
            return null; // Don't send email
        }

        // Convert oldEventData array back to object-like structure for the view
        $oldEvent = $this->oldEventData ? (object) $this->oldEventData : null;

        $subject = 'Event Notification';
        if ($this->isCancelled) {
            $subject = 'Event Cancelled';
        } elseif ($this->isUpdate) {
            $subject = 'Event Updated';
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

        return $this->subject($subject)
                    ->view('Mails.event_notification')
                    ->with([
                        'event' => $event,
                        'student' => $student,
                        'isUpdate' => $this->isUpdate,
                        'oldEvent' => $oldEvent,
                        'isCancelled' => $this->isCancelled,
                        'targetDepartments' => $targetDepartments,
                        'targetSections' => $targetSections,
                        'targetFacultyNames' => $targetFacultyNames,
                    ]);
    }
}