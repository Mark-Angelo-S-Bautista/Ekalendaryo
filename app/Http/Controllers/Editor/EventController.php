<?php
namespace App\Http\Controllers\Editor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

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
        ]);

        $location = $request->location === 'Other'
            ? $request->other_location
            : $request->location;

        Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $location, // use the processed value
            'school_year' => 'SY.2025-2026',
            'target_year_levels' => $validated['target_year_levels'] ?? [],
            'department' => Auth::user()->department,
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

        $event = Event::findOrFail($id);
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
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
}