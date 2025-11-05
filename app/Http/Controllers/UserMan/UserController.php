<?php

namespace App\Http\Controllers\UserMan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;

class UserController extends Controller
{
    public function adduser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'userId' => 'required|string|unique:users,userId',
            'email' => 'required|email|unique:users,email',
            'department' => 'required|in:BSIS/ACT,BSOM,BSAIS,BTVTED,BSCA,DHRMT,HB',
            'yearlevel' => 'required|in:1stYear,2ndYear,3rdYear,4thYear',
            'section' => 'required|string|max:1',
            'role' => 'required|in:Editor,UserManagement,Viewer',
        ]);

        User::create([
            'name' => $request->name,
            'title' => $request->title,
            'userId' => $request->userId,
            'email' => $request->email,
            'department' => $request->department,
            'yearlevel' => $request->yearlevel,
            'section' => $request->section,
            'role' => $request->role,
            'password' => Hash::make('password'), // default password
        ]);

        return redirect()->route('UserManagement.users')->with('success', 'User added successfully!');
    }
    public function edit($id)// ADDS DEPARTMENT
    {
        $user = User::findOrFail($id);
        $departments = Department::all(); // adjust if your model is named differently

        return view('UserManagement.users.editUser', compact('user', 'departments'))->with('success', 'Department Added Successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'userId' => 'required|string|unique:users,userId,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'department' => 'required|string',
            'yearlevel' => 'required|string',
            'section' => 'required|string|max:1',
            'role' => 'required|string',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'title' => $request->title,
            'userId' => $request->userId,
            'email' => $request->email,
            'department' => $request->department,
            'yearlevel' => $request->yearlevel,
            'section' => $request->section,
            'role' => $request->role,
        ]);

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
}
