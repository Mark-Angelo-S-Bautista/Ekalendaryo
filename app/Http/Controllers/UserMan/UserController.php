<?php

namespace App\Http\Controllers\UserMan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class UserController extends Controller
{
    public function adduser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'userId' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string',
            'department' => 'required|string',
            'title' => 'nullable|string|max:255',
            'yearlevel' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'password' => 'required|string|min:6',
        ]);

        // Check if user with same name + userId exists
        $exists = User::where('name', $request->name)
                    ->where('userId', $request->userId)
                    ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => ['User with this Name and ID already exists.'],
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()->all(),
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'userId' => $request->userId,
            'email' => $request->email,
            'role' => $request->role,
            'department' => $request->department,
            'title' => $request->title,
            'yearlevel' => $request->yearlevel,
            'section' => $request->section,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User added successfully!',
            'user' => $user,
        ]);
    }
    public function edit($id)// ADDS DEPARTMENT
    {
        $user = User::findOrFail($id);
        $departments = Department::all(); // adjust if your model is named differently

        return view('UserManagement.users.editUser', compact('user', 'departments'))->with('success', 'Department Added Successfully');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'userId' => 'required|string|unique:users,userId,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'department' => 'required|string',
            'yearlevel' => 'nullable|string',
            'section' => 'nullable|string|max:1',
            'role' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()->all()
                ]);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = User::findOrFail($id);
        $user->update($request->only([
            'name', 'title', 'userId', 'email', 'department', 'yearlevel', 'section', 'role'
        ]));

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully!',
                'user' => $user
            ]);
        }

        return redirect()->route('UserManagement.users')->with('success', 'User updated successfully!');
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('UserManagement.users')->with('success', 'User deleted successfully!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');

        // Open CSV using League\Csv (install via composer require league/csv)
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0); // first row as header
        $defaultpassword = 'password';

        $records = $csv->getRecords(); // iterable

        foreach ($records as $record) {
            // Example CSV columns: name,userId,email,department,yearlevel,section,role,password
            User::updateOrCreate(
                ['userId' => $record['userId']], // match by userId
                [
                    'name' => $record['name'],
                    'title' => $record['title'],
                    'email' => $record['email'],
                    'department' => $record['department'] ?? null,
                    'yearlevel' => $record['yearlevel'] ?? null,
                    'section' => $record['section'] ?? null,
                    'role' => $record['role'] ?? 'Viewer',
                    'password' => Hash::make($record['password'] ?? $defaultpassword), // default password
                ]
            );
        }

        return redirect()->back()->with('success', 'Users imported successfully!');
    }
    public function search(Request $request)
    {
        $query = $request->get('query', '');

        // We no longer need the user or department for this query
        // $user = Auth::user();
        // $dept = $user->department;

        $events = Event::query() // Start with a clean Event query
            // ->where(function ($q) use ($dept) {  <-- REMOVE THIS BLOCK
            //     $q->where('department', $dept)
            //     ->orWhere('department', 'OFFICES');
            // })                                     <-- REMOVE THIS BLOCK
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('title', 'like', "%{$query}%")
                        ->orWhere('location', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->orderBy('date', 'asc')
            ->get();

        return response()->json(['events' => $events]);
    }
}
