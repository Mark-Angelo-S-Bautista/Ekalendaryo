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
    public function edit($id)// ADDS DEPARTMENT
    {
        $user = User::findOrFail($id);
        $departments = Department::all(); // adjust if your model is named differently

        return view('UserManagement.users.editUser', compact('user', 'departments'))->with('success', 'Department Added Successfully');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'userId' => 'required|string|max:255|unique:users,userId,' . $id,
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'department' => 'required|string|max:255',
                'yearlevel' => 'nullable|string|max:50',
                'section' => 'nullable|string|max:50',
                'role' => 'required|string|max:255',
            ],
            [
                // Custom error messages
                'name.required' => 'The name field is required.',
                'name.max' => 'The name may not be greater than 255 characters.',

                'title.required' => 'The title field is required.',
                'title.max' => 'The title may not be greater than 255 characters.',

                'userId.required' => 'The user ID field is required.',
                'userId.unique' => 'This user ID already exists.',
                'userId.max' => 'The user ID may not be greater than 255 characters.',

                'email.required' => 'The email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already taken.',
                'email.max' => 'The email may not be greater than 255 characters.',

                'department.required' => 'The department field is required.',
                'department.max' => 'The department may not be greater than 255 characters.',

                'yearlevel.max' => 'The year level may not be greater than 50 characters.',

                'section.max' => 'The section may not be greater than 50 characters.',

                'role.required' => 'The role field is required.',
                'role.max' => 'The role may not be greater than 255 characters.',
            ]
        );

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()->toArray(), // IMPORTANT: plain array
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $user = User::findOrFail($id);

        $user->update($request->only([
            'name', 'title', 'userId', 'email', 'department', 'yearlevel', 'section', 'role',
        ]));

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully!',
                'user' => $user,
            ]);
        }

        return redirect()
            ->route('UserManagement.users')
            ->with('success', 'User updated successfully!');
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
