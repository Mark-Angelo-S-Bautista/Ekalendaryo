<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Department;
use App\Models\ActivityLog;
use App\Models\SchoolYear;
use App\Mail\EventNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/** @var \App\Models\User $user */

class EventController extends Controller
{
    // =========================================================================
    // STORE FUNCTION (Correct - associates event with Auth::id())
    // =========================================================================
    public function store(Request $request)
    {
        // ==============================
        // Validate input
        // ==============================
        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:100',
            'more_details' => 'nullable|string',
            'date' => 'required|date|after:today',

            'start_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($value < '07:00' || $value > '17:00') {
                        $fail('The start time must be between 7:00 AM and 5:00 PM.');
                    }
                },
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value < '07:00' || $value > '17:00') {
                        $fail('The end time must be between 7:00 AM and 5:00 PM.');
                    }
                    if ($request->start_time && $value <= $request->start_time) {
                        $fail('The end time must be after the start time.');
                    }
                },
            ],

            'location' => 'required|string|max:50',
            'other_location' => 'nullable|string|max:50|required_if:location,Other',

            'target_year_levels' => 'nullable|array',
            'target_department'  => 'nullable|array',
            'target_users'       => 'nullable|string',
            'target_faculty'     => 'nullable|array',
            'target_sections'    => 'nullable|array',
        ]);

        // ==============================
        // Get active school year (DYNAMIC)
        // ==============================
        $activeSchoolYear = SchoolYear::where('is_active', 1)->first();

        if (!$activeSchoolYear) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['school_year' => 'No active school year is set. Please contact the administrator.']);
        }

        // ==============================
        // Determine final location
        // ==============================
        $location = $validated['location'] === 'Other'
            ? $validated['other_location']
            : $validated['location'];

        // ==============================
        // Check for schedule conflict
        // ==============================
        $conflict = Event::where('date', $validated['date'])
            ->where('location', $location)
            ->where('status', '!=', 'cancelled') // ✅ ignore cancelled events
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                        ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->first();

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->with('conflict_event', [
                    'title' => $conflict->title,
                    'date' => $conflict->date,
                    'start_time' => $conflict->start_time,
                    'end_time' => $conflict->end_time,
                    'location' => $conflict->location,
                    'department' => $conflict->department,
                ]);
        }

        // ==============================
        // Create Event
        // ==============================
        $event = Event::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'more_details' => $validated['more_details'] ?? null,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $location,

            // ✅ DYNAMIC SCHOOL YEAR
            'school_year' => $activeSchoolYear->school_year,

            // JSON fields
            'target_year_levels' => $validated['target_year_levels'] ?? [],
            'target_department'  => $validated['target_department'] ?? [],
            'target_users'       => $validated['target_users'] ?? null,
            'target_faculty'     => $validated['target_faculty'] ?? [],
            'target_sections'    => $validated['target_sections'] ?? [],

            'department' => Auth::user()->department,
            'status' => 'upcoming',
        ]);

        // ==============================
        // Send notifications
        // ==============================
        if ($event->department === 'OFFICES') {
            $this->sendEmailsForOfficesEvent($event);
        } else {
            $this->sendEmailsForEvent($event);
        }

        // ==============================
        // Activity Log
        // ==============================
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action_type' => 'created',
            'model_type' => 'Event',
            'model_id' => $event->id,
            'description' => [
                'title' => $event->title,
                'event_date' => $event->date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'location' => $event->location,
                'event_description' => $event->description,
            ],
        ]);

        return redirect()->back()->with('success', 'Event created successfully!');
    }

    //SEND THE MAIL WHEN CREATING AN EVENT
    private function sendEmailsForEvent(Event $event, bool $isUpdate = false, $oldEvent = null, bool $isCancelled = false)
    {
        // =====================================================
        // Decide which event data to use for filtering
        // =====================================================
        $eventData = ($isCancelled && $oldEvent) ? $oldEvent : $event;

        // Extract and normalize target data
        $targetUsers = $eventData->target_users ?? null;
        $targetYearLevels = (array) ($eventData->target_year_levels ?? []);
        $targetFacultyIds = (array) ($eventData->target_faculty ?? []);
        $targetSections = (array) ($eventData->target_sections ?? []);
        $eventCreatorDepartment = $eventData->department ?? Auth::user()->department;

        $recipients = collect();

        // =====================================================
        // SCENARIO 1 & 3: Targeting "Students"
        // =====================================================
        if ($targetUsers === 'Students') {
            // Get students from selected sections AND year levels
            $students = User::where('title', 'Student')
                ->whereIn('section', $targetSections)
                ->get()
                ->filter(function ($student) use ($targetYearLevels) {
                    $studentYear = strtolower(str_replace(' ', '', $student->yearlevel ?? ''));
                    $allowedLevels = array_map(fn($lvl) => strtolower(str_replace(' ', '', $lvl)), $targetYearLevels);
                    return in_array($studentYear, $allowedLevels);
                });

            $recipients = $recipients->merge($students);

            // Add ONLY selected faculty (if any)
            if (!empty($targetFacultyIds)) {
                $selectedFaculty = User::whereIn('id', $targetFacultyIds)
                    ->where('title', 'Faculty')
                    ->get();
                $recipients = $recipients->merge($selectedFaculty);
            }

            // Add Department Head of same department
            $deptHead = User::where('title', 'Department Head')
                ->where('department', $eventCreatorDepartment)
                ->first();
            if ($deptHead) {
                $recipients = $recipients->merge([$deptHead]);
            }
        }
        // =====================================================
        // SCENARIO 2: Targeting "Faculty"
        // =====================================================
        elseif ($targetUsers === 'Faculty') {
            // Add ALL faculty from the same department
            $allFaculty = User::where('title', 'Faculty')
                ->where('department', $eventCreatorDepartment)
                ->get();
            $recipients = $recipients->merge($allFaculty);

            // Add Department Head of same department
            $deptHead = User::where('title', 'Department Head')
                ->where('department', $eventCreatorDepartment)
                ->first();
            if ($deptHead) {
                $recipients = $recipients->merge([$deptHead]);
            }
        }

        // Remove duplicates by email
        $recipients = $recipients->unique('email');

        // =====================================================
        // Send emails
        // =====================================================
        $eventToSend = $isCancelled && $oldEvent ? $oldEvent : $event;

        foreach ($recipients as $user) {
            if (!empty($user->email)) {
                Mail::to($user->email)->send(
                    new EventNotificationMail($eventToSend, $user, $isUpdate, $oldEvent, $isCancelled)
                );
            }
        }
    }

    /**
     * Send emails for OFFICES-created events based on target fields.
     */
    private function sendEmailsForOfficesEvent(Event $event, bool $isUpdate = false, $oldEvent = null, bool $isCancelled = false)
    {
        // Start with all users
        $users = User::query();

        // Filter by target_users (from event)
        switch ($event->target_users) {
            case 'Students':
                $users->where('title', 'Student');
                break;
            case 'Faculty':
                $users->whereIn('title', ['Faculty', 'Department Head']);
                break;
            case 'Department Heads':
                $users->where('title', 'Department Head');
                break;
            default:
                // If no target_users is specified, no filtering by role
                break;
        }

        // Filter by target_department if set and not "All"
        if (!empty($event->target_department) && !in_array('All', $event->target_department)) {
            $users->whereIn('department', $event->target_department);
        }

        // Get users from database
        $users = $users->get();

        // Filter Students further by year levels if applicable
        if ($event->target_users === 'Students' && !empty($event->target_year_levels)) {
            $normalizedYearLevels = array_map(fn($lvl) => strtolower(str_replace(' ', '', $lvl)), $event->target_year_levels);

            $users = $users->filter(function ($user) use ($normalizedYearLevels) {
                $userYearLevel = strtolower(str_replace(' ', '', $user->yearlevel ?? ''));
                return in_array($userYearLevel, $normalizedYearLevels);
            });
        }

        // Send emails
        foreach ($users as $user) {
            Mail::to($user->email)->send(new EventNotificationMail($event, $user, $isUpdate, $oldEvent, $isCancelled));
        }
    }


    // =========================================================================
    // MODIFIED: STRICT User-ID filtering applied.
    // =========================================================================
    public function index()
    {
        $userId = Auth::id();

        $events = Event::with('user')
            ->where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->get()
            ->filter(function ($event) {
                return in_array($event->computed_status, ['upcoming', 'ongoing']);
            });

        $departments = Department::all();

        // ✅ Get all faculty
        $faculty = User::where('title', 'Faculty')->get();

        // ✅ Get distinct sections (from users table)
        $sections = User::whereNotNull('section')->distinct('section')->pluck('section');

        return view('Editor.manageEvents', compact('events', 'departments', 'faculty', 'sections'));
    }

    // =========================================================================
    // MODIFIED: STRICT User-ID filtering applied for security.
    // =========================================================================
    public function edit($id)
    {
        $userId = Auth::id();

        // Find event ONLY if its ID and user_id match
        $event = Event::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail(); // Throws 404 if not found or not owned

        $departments = Department::all();

        // ✅ Get all faculty
        $faculty = User::where('title', 'Faculty')->get();

        // ✅ Get distinct sections (from users table)
        $sections = User::whereNotNull('section')->distinct('section')->pluck('section');

        return view('Editor.editEvents', compact('event', 'departments', 'faculty', 'sections'));
    }

    // =========================================================================
    // MODIFIED: STRICT User-ID filtering applied for security.
    // =========================================================================
    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        // Find event ONLY if its ID and user_id match
        $event = Event::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();

        // ... (validation and conflict check logic is correct) ...

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'more_details' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'target_year_levels' => 'nullable|array',
            'target_department' => 'nullable|array',
            'target_users' => 'nullable|string',
            'target_faculty' => 'nullable|array',
            'target_sections' => 'nullable|array',
            'other_location' => 'nullable|string|max:255', // Added back 'other_location' validation
        ]);

        $location = $request->location === 'Other'
            ? $request->other_location
            : $request->location;

        // Check for conflicting events (excluding the current event)
        $conflict = Event::where('id', '!=', $event->id)
            ->where('date', $validated['date'])
            ->where('location', $location)
            ->where('status', '!=', 'cancelled') // ✅ ignore cancelled events
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                        ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->first();

        if ($conflict) {
            return redirect()->back()
                ->withInput()
                ->with('conflict_event', [
                    'title' => $conflict->title,
                    'date' => $conflict->date,
                    'start_time' => $conflict->start_time,
                    'end_time' => $conflict->end_time,
                    'location' => $conflict->location,
                    'department' => $conflict->department,
                ]);
        }

        $oldEvent = $event->replicate();

        // No conflict, update the event
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'more_details' => $validated['more_details'] ?? null,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $location,
            'target_year_levels' => $validated['target_year_levels'] ?? [],
            'target_department' => $request->target_department ?? [],
            'target_users' => $request->target_users ?? null,
            'target_faculty' => $validated['target_faculty'] ?? [],
            'target_sections' => $validated['target_sections'] ?? [],
        ]);

        // Send updated notification
        if ($event->department === 'OFFICES') {
            $this->sendEmailsForOfficesEvent($event, true, $oldEvent);
        } else {
            $this->sendEmailsForEvent($event, true, $oldEvent);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action_type' => 'edited',
            'model_type' => 'Event',
            'model_id' => $event->id,
            'description' => [
                'title' => [
                    'old' => $oldEvent->title,
                    'new' => $event->title,
                ],
                'event_date' => [
                    'old' => $oldEvent->date,
                    'new' => $event->date,
                ],
                'start_time' => [
                    'old' => $oldEvent->start_time,
                    'new' => $event->start_time,
                ],
                'end_time' => [
                    'old' => $oldEvent->end_time,
                    'new' => $event->end_time,
                ],
                'location' => [
                    'old' => $oldEvent->location,
                    'new' => $event->location,
                ],
                'event_description' => [
                    'old' => $oldEvent->description,
                    'new' => $event->description,
                ],
            ],
        ]);

        return redirect()->route('Editor.index')->with('success', 'Event Updated and Emails sent Successfully!');
    }

    // =========================================================================
    // MODIFIED: STRICT User-ID filtering applied for security.
    // =========================================================================
    public function destroy($id)
    {
        $userId = Auth::id();

        // Find event ONLY if its ID and user_id match
        $event = Event::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // 🔒 Prevent double cancellation
        if ($event->status === 'cancelled') {
            return redirect()->back()->with('error', 'This event is already cancelled.');
        }

        // Send cancellation emails
        if ($event->department === 'OFFICES') {
            $this->sendEmailsForOfficesEvent($event, false, null, true);
        } else {
            $this->sendEmailsForEvent($event, false, null, true);
        }

        // ✅ CANCEL instead of DELETE
        $event->update([
            'status' => 'cancelled',
        ]);

        // Activity Log
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action_type' => 'cancelled', // 👈 clearer than "deleted"
            'model_type' => 'Event',
            'model_id' => $event->id,
            'description' => [
                'title' => $event->title,
                'event_date' => $event->date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'location' => $event->location,
                'event_description' => $event->description,
            ],
        ]);

        return redirect()->back()->with('success', 'Event cancelled and email sent successfully.');
    }

    // =========================================================================
    // checkConflict function is correct (checks against all events for scheduling)
    // =========================================================================
    public function checkConflict(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string',
        ]);

        $conflict = Event::where('date', $request->date)
            ->where('location', $request->location)
            ->where('status', '!=', 'cancelled') // ✅ Ignore cancelled events
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->start_time)
                            ->where('end_time', '>', $request->end_time);
                    });
            })
            ->first();

        if ($conflict) {
            return response()->json([
                'conflict' => true,
                'event' => [
                    'title' => $conflict->title,
                    'date' => $conflict->date,
                    'start_time' => $conflict->start_time,
                    'end_time' => $conflict->end_time,
                    'location' => $conflict->location,
                    'department' => $conflict->department,
                ]
            ]);
        }

        return response()->json(['conflict' => false]);
    }
}