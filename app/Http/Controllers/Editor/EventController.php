<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Make sure this is imported

/** @var \App\Models\User $user */

class EventController extends Controller
{
    // =========================================================================
    // This function is correct, no changes needed.
    // =========================================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'target_year_levels' => 'nullable|array',
            'other_location' => 'nullable|string|max:255',
        ]);

        // Process location
        $location = $request->location === 'Other' ? $request->other_location : $request->location;

        // Check for conflicts
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

        // No conflict, create event
        Event::create([
            'user_id' => auth()->id(), // make sure user_id is fillable
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $location,
            'school_year' => 'SY.2025-2026',
            'target_year_levels' => $validated['target_year_levels'] ?? [],
            'department' => auth()->user()->department,
        ]);

        return redirect()->back()->with('success', 'Event created successfully!');
    }

    // =========================================================================
    // MODIFIED: This function now includes your new logic.
    // =========================================================================
    public function index()
    {
        $user = Auth::user();
        $dept = $user->department;
        $userId = $user->id;

        // Start query and *always* include 'user' for the dynamic tags
        $query = Event::with('user');

        // --- ADDED LOGIC ---
        if ($dept === 'OFFICES') {
            // If from OFFICES, only show events this user created
            $query->where('user_id', $userId);
        } else {
            // Otherwise, show their department's events + all 'OFFICES' events
            $query->where(function ($q) use ($dept) {
                $q->where('department', $dept)
                  ->orWhere('department', 'OFFICES');
            });
        }
        // --- END ADDED LOGIC ---

        $events = $query->orderBy('date', 'asc')->get();

        return view('Editor.manageEvents', compact('events'));
    }

    // =========================================================================
    // MODIFIED: Applied new logic for security.
    // =========================================================================
    public function edit($id)
    {
        $user = Auth::user();
        $dept = $user->department;
        $userId = $user->id;

        // Start query to find the event
        $query = Event::query();

        // --- ADDED LOGIC ---
        // Apply same rules as index() to ensure user is authorized
        if ($dept === 'OFFICES') {
            $query->where('user_id', $userId);
        } else {
            $query->where(function ($q) use ($dept) {
                $q->where('department', $dept)
                  ->orWhere('department', 'OFFICES');
            });
        }
        // --- END ADDED LOGIC ---

        // Find the event by ID *within the authorized set*
        $event = $query->findOrFail($id);

        return view('Editor.editEvents', compact('event'));
    }

    // =========================================================================
    // MODIFIED: Applied new logic for security.
    // =========================================================================
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $dept = $user->department;
        $userId = $user->id;

        // Start query to find the event
        $query = Event::query();

        // --- ADDED LOGIC ---
        // Apply same rules as index() to ensure user is authorized
        if ($dept === 'OFFICES') {
            $query->where('user_id', $userId);
        } else {
            $query->where(function ($q) use ($dept) {
                $q->where('department', $dept)
                  ->orWhere('department', 'OFFICES');
            });
        }
        // --- END ADDED LOGIC ---

        // Find the event by ID *within the authorized set*
        $event = $query->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'target_year_levels' => 'nullable|array',
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

        // No conflict, update the event
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $location,
            'target_year_levels' => $validated['target_year_levels'] ?? [],
        ]);

        return redirect()->route('Editor.index')->with('success', 'Event updated successfully!');
    }

    // =========================================================================
    // MODIFIED: Applied new logic for security.
    // =========================================================================
    public function destroy($id)
    {
        $user = Auth::user();
        $dept = $user->department;
        $userId = $user->id;

        // Start query to find the event
        $query = Event::query();

        // --- ADDED LOGIC ---
        // Apply same rules as index() to ensure user is authorized
        if ($dept === 'OFFICES') {
            $query->where('user_id', $userId);
        } else {
            $query->where(function ($q) use ($dept) {
                $q->where('department', $dept)
                  ->orWhere('department', 'OFFICES');
            });
        }
        // --- END ADDED LOGIC ---

        // Find the event by ID *within the authorized set*
        $event = $query->findOrFail($id);

        $event->delete();

        return redirect()->back()->with('success', 'Event deleted successfully!');
    }

    // =========================================================================
    // This function is correct, no changes needed.
    // It should check conflicts against ALL events in the database.
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