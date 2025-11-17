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
        if ($event->department === 'OFFICES') {
            $this->sendEmailsForOfficesEvent($event);
        } else {
            $this->sendEmailsForEvent($event);
        }


        return redirect()->back()->with('success', 'Event created and emails sent successfully!');
    }

    //SEND THE MAIL WHEN CREATING AN EVENT
    private function sendEmailsForEvent(Event $event, bool $isUpdate = false, $oldEvent = null, bool $isCancelled = false)
    {
        // 1. Normalize Target Data
        $targetDepartments = (array) ($event->target_department ?? []);
        if (is_string($event->target_department)) {
            $targetDepartments = array_map('trim', explode(',', $event->target_department));
        }
        $targetDepartments = array_filter($targetDepartments);

        $targetRoles = (array) ($event->target_users ?? []);
        if (is_string($event->target_users)) {
            $targetRoles = array_map('trim', explode(',', $event->target_users));
        }
        $targetRoles = array_filter($targetRoles);

        $targetYearLevels = $event->target_year_levels ?? []; // Array of year levels or empty array

        $rolesToInclude = [];
        $includeStudents = false;
        
        // --- 2. Determine Role/Title Criteria based on target_users ---

        // Always include Department Heads, as they are part of the 'Faculty' scope
        $rolesToInclude[] = 'Department Head'; 
        
        $isFacultyTargeted = in_array('Faculty', $targetRoles);
        $isStudentsTargeted = in_array('Viewer', $targetRoles) || in_array('Students', $targetRoles); // Assuming 'Students' maps to 'Viewer' role/title

        if ($isFacultyTargeted) {
            // Rule: If target_users is Faculty, send to Faculty and Department Head
            $rolesToInclude[] = 'Faculty';
        } elseif ($isStudentsTargeted) {
            // Rule: If target_users is Students (Viewer), send to Viewer (Students) and Faculty
            $rolesToInclude[] = 'Student';
            $rolesToInclude[] = 'Faculty';
            $includeStudents = true;
        } else {
            // If neither Faculty nor Students are targeted, include other explicitly named roles
            foreach ($targetRoles as $role) {
                if (!in_array($role, ['Faculty', 'Viewer', 'Students', 'Department Head'])) {
                    $rolesToInclude[] = $role;
                }
            }
        }

        $query = User::query();
        
        // Filter by the collected unique titles/roles
        $query->whereIn('title', array_unique($rolesToInclude));

        // --- 3. Filter by Target Departments (if specified) ---
        if (!empty($targetDepartments) && !in_array('ALL', array_map('strtoupper', $targetDepartments))) {
            $query->where(function ($q) use ($targetDepartments, $event) {
                $q->whereIn('department', $targetDepartments)
                // Keep users from the event's creation department as a broad measure
                ->orWhere('department', $event->department); 
            });
        }

        // --- 4. Execute Query and Apply Year Level Filtering (Post-Query) ---
        $users = $query->get();

        $recipients = $users->filter(function ($user) use ($targetYearLevels, $includeStudents) {
            // All non-Viewer roles (Faculty, Department Head, etc.) are included if they passed steps 2 & 3.
            if (strtolower($user->title) !== 'viewer') {
                return true; 
            }

            // --- Logic for Students (Viewers) ---
            
            // If students were not included in the initial query logic (Step 2).
            if (!$includeStudents) {
                return false;
            }

            // If 'Viewer' was targeted AND targetYearLevels is empty, include all students.
            if (empty($targetYearLevels)) {
                return true;
            }

            // Apply specific Year Level filtering for students.
            $studentYearLevel = strtolower(str_replace(' ', '', $user->yearlevel ?? ''));
            $normalizedYearLevels = array_map(fn($lvl) => strtolower(str_replace(' ', '', $lvl)), $targetYearLevels);
            
            return in_array($studentYearLevel, $normalizedYearLevels);
        });

        // Ensure no duplicates based on email address
        $recipients = $recipients->unique('email');

        // ------------------------------
        // 5. SEND EMAILS
        // ------------------------------
        foreach ($recipients as $user) {
            Mail::to($user->email)->send(
                new EventNotificationMail($event, $user, $isUpdate, $oldEvent, $isCancelled)
            );
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
                $users->where('title', 'Faculty');
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

        $departments = Department::all();

        return view('Editor.editEvents', compact('event', 'departments'));
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
            'target_department' => $request->target_department ?? [],
            'target_users' => $request->target_users ?? null,
        ]);

        // Send updated notification
        if ($event->department === 'OFFICES') {
            $this->sendEmailsForOfficesEvent($event, true, $oldEvent);
        } else {
            $this->sendEmailsForEvent($event, true, $oldEvent);
        }

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
        if ($event->department === 'OFFICES'){
            $this->sendEmailsForOfficesEvent($event, false, null, true);
        }else{
            $this->sendEmailsForEvent($event, false, null, true);
        }
        

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