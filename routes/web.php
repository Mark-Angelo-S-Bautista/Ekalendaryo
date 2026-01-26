<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserMan\UserManagementController;
use App\Http\Controllers\Editor\EventController;
use App\Http\Controllers\Editor\EditorController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserMan\UserController;
use App\Http\Controllers\Viewer\ViewerController;
use App\Http\Controllers\UserMan\CalendarController;
use Illuminate\Support\Facades\Route;

// Group the routes without a 'prefix' so they start at the root URL (/)

Route::get('/', [LoginController::class, 'index'])->name('Auth.login');
Route::post('/', [LoginController::class, 'store'])->name('login');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::post('/password/otp-request', [PasswordResetController::class, 'requestOtp'])
    ->name('password.otp.request');

Route::get('/password/otp-verify', function () {
    return view('Auth.verify_otp');
})->name('password.otp.verify.page');

Route::post('/password/otp-verify', [PasswordResetController::class, 'verifyOtp'])
    ->name('password.otp.verify');

Route::get('/password/change', function () {
    return view('Auth.change_password');
})->name('password.change');

Route::post('/password/change', [PasswordResetController::class, 'changePassword'])
    ->name('password.change');

Route::get('/profile/verify-email-change/{token}',[EditorController::class, 'verifyNewEmail'])->name('profile.verifyNewEmail');


Route::middleware(['auth', 'backhistory'])->group(function () {
    // --- A. EDITOR ROUTES (Requires 'auth' + 'role.editor') ---
    // Applies the prefix 'editor/' and the route name prefix 'editor.'
    Route::middleware('role.editor')->prefix('editor')->name('Editor.')->group(function () {
        Route::get('/dashboard', [EditorController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/search', [EditorController::class, 'search'])->name('search');
        Route::get('/calendar', [EditorController::class, 'calendar'])->name('calendar');
        // Route::get('/ManageEvents', [EditorController::class, 'manageEvents'])->name('manageEvents');
        Route::get('/activity_log', [EditorController::class, 'activity_log'])->name('activity_log');
        Route::get('/history', [EditorController::class, 'history'])->name('history');
        Route::get('/event/{event}/feedback', [EditorController::class, 'getFeedback']);
        Route::post('/events/{event}/upload-report', [EditorController::class, 'uploadReport'])->name('uploadReport');
        Route::post('/events/{event}/remove-report', [EditorController::class, 'removeReport'])->name('removeReport');
        Route::get('/events/{event}/download-report', [EditorController::class, 'downloadReport'])->name('downloadReport');
        Route::get('/archive', [EditorController::class, 'archive'])->name('archive');
        Route::get('/profile', [EditorController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [EditorController::class, 'updateProfile'])->name('editor.profile.update');
        Route::post('/profile/update-email', [EditorController::class, 'updateEmail'])->name('editor.profile.updateEmail');
        Route::post('/profile/update-password', [EditorController::class, 'updatePassword'])->name('editor.profile.updatePassword');
        Route::post('/profile/update-email',[EditorController::class, 'updateEmail'])->name('profile.updateEmail');
        Route::post('/logout', [EditorController::class, 'destroy'])->name('logout');

        //Insert event into Database
        Route::get('/manageEvents', [EventController::class, 'index'])->name('index');
        Route::post('/manageEvents', [EventController::class, 'store'])->name('store');
        Route::post('/check-conflict', [EventController::class, 'checkConflict'])->name('checkConflict');
        Route::get('/manageEvents/{id}', [EventController::class, 'edit'])->name('editEvent');
        Route::put('/manageEvents/{id}', [EventController::class, 'update'])->name('update');
        Route::delete('/manageEvents/{id}', [EventController::class, 'destroy'])->name('destroy');
    });

    // --- B. USER MANAGEMENT ROUTES (Requires 'auth' + 'role.usermanagement') ---
    // Applies the prefix 'usermanagement/' and the route name prefix 'usermanagement.'
    Route::middleware('role.usermanagement')->prefix('usermanagement')->name('UserManagement.')->group(function () {
        Route::get('/dashboard', [UserManagementController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/search', [UserController::class, 'search'])->name('search');
        Route::get('/calendar', [UserManagementController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [UserManagementController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [UserManagementController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/update-email', [UserManagementController::class, 'updateEmail'])->name('profile.updateEmail');
        Route::post('/profile/update-password', [UserManagementController::class, 'updatePassword'])->name('profile.updatePassword');
        Route::post('/profile/update-email',[UserManagementController::class, 'updateEmail'])->name('profile.updateEmail');

        Route::get('/users', [UserManagementController::class, 'users'])->name('users');
        Route::get('/users/search', [UserManagementController::class, 'search'])->name('search');
        Route::post('/adduser', [UserManagementController::class, 'adduser'])->name('adduser');
        Route::post('/addDepartment', [UserManagementController::class, 'addDepartment'])->name('adddepartment');
        // Route::delete('/deletedepartment/{id}', [UserManagementController::class, 'deleteDepartment'])->name('deletedepartment');
        // Show edit form
        Route::get('/edituser/{id}', [UserController::class, 'edit'])->name('edit');
        // Handle update
        Route::put('/edituser/{id}', [UserController::class, 'update'])->name('update'); 
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('delete');
        //Insert users using CSV file
        Route::post('/users', [UserController::class, 'import'])->name('import');
        //Change School Year
        Route::post('/dashboard', [UserManagementController::class, 'changeSchoolYear'])->name('changeSchoolYear');

        Route::get('/activity_log', [UserManagementController::class, 'activity_log'])->name('activity_log');
        Route::get('/history', [UserManagementController::class, 'history'])->name('history');
        Route::get('/archive', [UserManagementController::class, 'archive'])->name('archive');
        Route::post('/logout', [UserManagementController::class, 'destroy'])->name('logout');
    });

    // --- C. VIEWER ROUTES (Requires 'auth' + 'role.viewer') ---
    // Applies the prefix 'usermanagement/' and the route name prefix 'usermanagement.'
    Route::middleware('role.viewer')->prefix('viewer')->name('Viewer.')->group(function () {
        Route::get('/dashboard', [ViewerController::class, 'dashboard'])->name('dashboard');
        Route::post('/dashboard/{event}/attend', [ViewerController::class, 'attend'])->name('attend');
        Route::get('/calendar', [ViewerController::class, 'calendar'])->name('calendar');
        Route::get('/notifications', [ViewerController::class, 'notifications'])->name('notifications');
        Route::get('/history', [ViewerController::class, 'history'])->name('history');
        Route::post('/events/feedback', [ViewerController::class, 'storeFeedback']);
        Route::get('/profile', [ViewerController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [ViewerController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/update-email', [ViewerController::class, 'updateEmail'])->name('profile.updateEmail');
        Route::post('/profile/update-password', [ViewerController::class, 'updatePassword'])->name('profile.updatePassword');
        Route::post('/profile/update-email',[ViewerController::class, 'updateEmail'])->name('profile.updateEmail');
        Route::post('/logout', [ViewerController::class, 'destroy'])->name('logout');
    });

});