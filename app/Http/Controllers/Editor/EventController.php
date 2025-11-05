<?php
namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
/** @var \App\Models\User $user */

class EventController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
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

    public function index()
    {
        $userDepartment = Auth::user()->department;

        $events = Event::where('department', $userDepartment)
                    ->orderBy('date', 'asc')
                    ->get();

        return view('Editor.manageEvents', compact('events'));
    }
    
    public function edit($id)
    {
        $event = Event::where('id', $id)
                  ->where('department', Auth::user()->department)
                  ->firstOrFail();

        return view('Editor.editEvents', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::where('id', $id)
                  ->where('department', Auth::user()->department)
                  ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
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

    public function destroy($id)
    {
        $event = Event::where('id', $id)
                  ->where('department', Auth::user()->department)
                  ->firstOrFail();

        $event->delete();

        return redirect()->back()->with('success', 'Event deleted successfully!');
    }

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
                ]
            ]);
        }

        return response()->json(['conflict' => false]);
    }
}