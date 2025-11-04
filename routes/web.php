<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserMan\UserManagementController;
use App\Http\Controllers\Editor\EditorController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UserMan\UserController;
use App\Http\Controllers\Viewer\ViewerController;
use Illuminate\Support\Facades\Route;

// Group the routes without a 'prefix' so they start at the root URL (/)

Route::get('/', [LoginController::class, 'index'])->name('Auth.login');
Route::post('/', [LoginController::class, 'store'])->name('login');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::post('/password-reset', [PasswordResetController::class, 'sendResetLink'])
    ->name('password.reset.request');


Route::middleware('auth')->group(function () {
    // --- A. EDITOR ROUTES (Requires 'auth' + 'role.editor') ---
    // Applies the prefix 'editor/' and the route name prefix 'editor.'
    Route::middleware('role.editor')->prefix('editor')->name('Editor.')->group(function () {
        Route::get('/dashboard', [EditorController::class, 'dashboard'])->name('dashboard');
        Route::get('/calendar', [EditorController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [EditorController::class, 'profile'])->name('profile');
        Route::post('/logout', [EditorController::class, 'destroy'])->name('logout');
    });

    // --- B. USER MANAGEMENT ROUTES (Requires 'auth' + 'role.usermanagement') ---
    // Applies the prefix 'usermanagement/' and the route name prefix 'usermanagement.'
    Route::middleware('role.usermanagement')->prefix('usermanagement')->name('UserManagement.')->group(function () {
        Route::get('/dashboard', [UserManagementController::class, 'dashboard'])->name('dashboard');
        Route::get('/calendar', [UserManagementController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [UserManagementController::class, 'profile'])->name('profile');

        Route::get('/users', [UserManagementController::class, 'users'])->name('users');
        Route::get('/users/search', [UserManagementController::class, 'search'])->name('search');
        Route::post('/adduser', [UserController::class, 'adduser'])->name('adduser');
        Route::post('/addDepartment', [UserManagementController::class, 'addDepartment'])->name('adddepartment');

        Route::get('/activity_log', [UserManagementController::class, 'activity_log'])->name('activity_log');
        Route::get('/history', [UserManagementController::class, 'history'])->name('history');
        Route::get('/archive', [UserManagementController::class, 'archive'])->name('archive');
        Route::post('/logout', [UserManagementController::class, 'destroy'])->name('logout');
    });

    // --- C. VIEWER ROUTES (Requires 'auth' + 'role.viewer') ---
    // Applies the prefix 'usermanagement/' and the route name prefix 'usermanagement.'
    Route::middleware('role.viewer')->prefix('viewer')->name('Viewer.')->group(function () {
        Route::get('/dashboard', [ViewerController::class, 'dashboard'])->name('dashboard');
        Route::get('/calendar', [ViewerController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [ViewerController::class, 'profile'])->name('profile');
    });

});