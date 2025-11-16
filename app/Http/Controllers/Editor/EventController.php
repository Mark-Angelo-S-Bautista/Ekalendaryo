<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Department;
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
        // Validate input
        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:100',
            'more_details' => 'nullable|string', // ✅ added field
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
            'target_year_levels' => 'nullable|array',
            'target_department'   => 'nullable|array',
            'target_users'        => 'nullable|string',
            'other_location' => 'nullable|string|max:50|required_if:location,Other',
        ]);

        // Determine final location
        $location = $request->location === 'Other' ? $request->other_location : $request->location;

        // Check for schedule conflict
        $conflict = Event::where('date', $validated['date'])
            ->where('location', $location)
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

        // ✅ Create event (with more_details)
        $event = Event::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'more_details' => $validated['more_details'] ?? null, // ✅ added field
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $location,
            'school_year' => 'SY.2025-2026',
            'target_year_levels' => $validated['target_year_levels'] ?? [],
            'target_department' => $validated['target_department'] ?? [],
            'target_users' => $request->target_users,           // string
            'department' => auth()->user()->department,
        ]);

        // Send notifications
        $this->sendEmailsForEvent($event);


        return redirect()->back()->with('success', 'Event created and emails sent successfully!');
    }

    //SEND THE MAIL WHEN CREATING AN EVENT
    private function sendEmailsForEvent(Event $event, bool $isUpdate = false, $oldEvent = null, bool $isCancelled = false)
    {
        if ($event->department === 'OFFICES') {
            return;
        }

        $dept = $event->department;
        $yearLevels = $event->target_year_levels;

        if (empty($yearLevels)) {
            $students = User::where('role', 'Viewer')
                            ->where('department', $dept)
                            ->get();
        } else {
            $normalizedYearLevels = array_map(function ($lvl) {
                return strtolower(str_replace(' ', '', $lvl));
            }, $yearLevels);

            $students = User::where('role', 'Viewer')
                            ->where('department', $dept)
                            ->get()
                            ->filter(function ($student) use ($normalizedYearLevels) {
                                $studentYearLevel = strtolower(str_replace(' ', '', $student->yearlevel ?? ''));
                                return in_array($studentYearLevel, $normalizedYearLevels);
                            });
        }

        foreach ($students as $student) {
            Mail::to($student->email)->send(new EventNotificationMail($event, $student, $isUpdate, $oldEvent, $isCancelled));
        }
    }

    // =========================================================================
    // MODIFIED: STRICT User-ID filtering applied.
    // =========================================================================
    public function index()
    {
        $userId = Auth::id();

        // ONLY show events where event's user_id matches the authenticated user's ID
        $events = Event::with('user')
                        ->where('user_id', $userId)
                        ->orderBy('date', 'asc')
                        ->get();

        // ✅ Get all departments
        $departments = Department::all();

        return view('Editor.manageEvents', compact('events', 'departments'));
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

        return view('Editor.editEvents', compact('event'));
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
            'other_location' => 'nullable|string|max:255', // Added back 'other_location' validation
        ]);

        $location = $request->location === 'Other'
            ? $request->other_location
            : $request->location;

        // Check for conflicting events (excluding the current event)
        $conflict = Event::where('id', '!=', $event->id)
            ->where('date', $validated['date'])
            ->where('location', $location)
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
        ]);

        // Send updated notification
        $this->sendEmailsForEvent($event, true, $oldEvent);

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
        
        // Send cancellation emails
        $this->sendEmailsForEvent($event, false, null, true);

        $event->delete();

        return redirect()->back()->with('success', 'Event Deleted and Email Sent Successfully!');
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