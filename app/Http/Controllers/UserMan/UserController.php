<?php

namespace App\Http\Controllers\UserMan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class UserController
{
    public function edit($id, Request $request)// ADDS DEPARTMENT
    {
        $user = User::findOrFail($id);
        $departments = Department::all(); // adjust if your model is named differently
        $isRestore = $request->has('restore') && $request->restore == '1';

        return view('UserManagement.users.editUser', compact('user', 'departments', 'isRestore'))->with('success', 'Department Added Successfully');
    }

    public function restore($id)
    {
        $user = User::findOrFail($id);

        // Check if user is in a restorable status
        if (!in_array($user->status, ['graduated', 'dropped', 'fired'])) {
            return redirect()->route('UserManagement.archive')->with('error', 'This user cannot be restored.');
        }

        // Redirect to edit page with restore flag
        return redirect()->route('UserManagement.edit', ['id' => $id, 'restore' => 1]);
    }

    public function update(Request $request, $id)
    {
        // Check if this is a restore operation
        $isRestore = $request->has('is_restore') && $request->is_restore == '1';

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
                'password' => 'nullable|string|min:6', // <- optional password field
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

                'password.min' => 'Password must be at least 6 characters.',
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
        $wasRestored = in_array($user->status, ['graduated', 'dropped', 'fired']);

        // Only update password if provided
        $updateData = $request->only([
            'name', 'title', 'userId', 'email', 'department', 'yearlevel', 'section', 'role'
        ]);

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        // If restoring, also update status and related fields
        if ($isRestore && $wasRestored) {
            $activeSY = SchoolYear::where('is_active', 1)->first();
            $updateData['status'] = 'active';
            $updateData['is_deleted'] = 0;
            $updateData['deleted_at'] = null;
            $updateData['deleted_school_year'] = null;
            if ($activeSY) {
                $updateData['school_year_id'] = $activeSY->id;
            }
        }

        $user->update($updateData);

        // Return success for restore operation
        if ($isRestore && $wasRestored) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User restored successfully!',
                    'redirect' => route('UserManagement.users'),
                ]);
            }
            return redirect()->route('UserManagement.users')->with('success', 'User restored successfully!');
        }

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
        $activeSY = SchoolYear::where('is_active', 1)->firstOrFail();

        $user->update([
            'status' => $user->title === 'Student' ? 'dropped' : 'fired',
            'is_deleted' => 1,
            'deleted_at' => now(),
            'deleted_school_year' => $activeSY->school_year,
            'school_year_id' => $activeSY->id
        ]);

        return redirect()->route('UserManagement.users')->with('success', 'User deleted successfully!');
    }

    public function import(Request $request)
    {
        // Validate CSV file
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt', // ensure it's a file
        ]);

        $file = $request->file('csv_file');

        // Use fopen in 'r' mode to avoid encoding issues
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0); // first row is header

        // Get active school year
        $activeSchoolYear = SchoolYear::where('is_active', 1)->first();
        $schoolYearId = $activeSchoolYear ? $activeSchoolYear->id : null;

        $defaultPassword = 'password';
        $records = $csv->getRecords();
        $importErrors = [];

        foreach ($records as $index => $record) {
            $rowNumber = $index + 1; // row 2 because row 1 is header
            $errors = [];

            // Trim whitespace from all fields
            $record = array_map('trim', $record);

            // Required fields
            if (empty($record['userId'])) {
                $errors[] = 'userId is required';
            }
            if (empty($record['email'])) {
                $errors[] = 'email is required';
            }

            // Check for duplicates in database
            if (!empty($record['userId']) && User::where('userId', $record['userId'])->exists()) {
                $errors[] = "Duplicate userId: {$record['userId']}";
            }
            if (!empty($record['email']) && User::where('email', $record['email'])->exists()) {
                $errors[] = "Duplicate email: {$record['email']}";
            }

            if (!empty($errors)) {
                $importErrors[] = [
                    'row' => $rowNumber,
                    'errors' => $errors,
                    'data' => $record,
                ];
                continue; // skip this row
            }

            // Insert user
            User::create([
                'userId' => $record['userId'],
                'name' => $record['name'] ?? null,
                'title' => $record['title'] ?? null,
                'email' => $record['email'],
                'department' => $record['department'] ?? null,
                'yearlevel' => $record['yearlevel'] ?? null,
                'section' => $record['section'] ?? null,
                'role' => $record['role'] ?? 'Viewer',
                'password' => Hash::make($record['password'] ?? $defaultPassword),
                'school_year_id' => $schoolYearId,
            ]);
        }

        if (!empty($importErrors)) {
            return redirect()->back()->with('importErrors', $importErrors);
        }

        return redirect()->back()->with('success', 'Users imported successfully!');
    }

    public function downloadTemplate()
    {
        // Path to your template file in storage/app/public/files or public/files
        $filePath = public_path('files/user_import_template.csv');

        if (!file_exists($filePath)) {
            abort(404, 'Template file not found.');
        }

        // Force download as CSV with correct headers
        return response()->download($filePath, 'user_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('query', '');
        $today = now()->toDateString(); // YYYY-MM-DD
        $limitDate = now()->addDays(30)->toDateString();

        $events = Event::with('user')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('title', 'like', "%{$query}%")
                        ->orWhere('location', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->get()
            ->filter(function ($event) use ($today, $limitDate) {

                $eventDate = $event->date; // already YYYY-MM-DD

                // ✅ ONGOING (status-based)
                if ($event->status === 'ongoing') {
                    return true;
                }

                // ✅ UPCOMING (date-based, next 30 days including today)
                if (
                    $event->status === 'upcoming' &&
                    $eventDate >= $today &&
                    $eventDate <= $limitDate
                ) {
                    return true;
                }

                return false;
            })
            ->sortBy('date')
            ->values();

        // Attach office_name for frontend
        $events->transform(function ($event) {
            $event->office_name = $event->user->office_name ?? null;
            return $event;
        });

        return response()->json(['events' => $events]);
    }
}
