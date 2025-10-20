<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserMan\UserManagementController;
use App\Http\Controllers\Editor\EditorController;
use Illuminate\Support\Facades\Route;

// Group the routes without a 'prefix' so they start at the root URL (/)

Route::get('/', [LoginController::class, 'index'])->name('Auth.login');
Route::post('/', [LoginController::class, 'store'])->name('login');

Route::middleware('auth')->group(function () {
    
    // --- A. EDITOR ROUTES (Requires 'auth' + 'role.editor') ---
    // Applies the prefix 'editor/' and the route name prefix 'editor.'
    Route::middleware('role.editor')->prefix('editor')->name('Editor.')->group(function () {
        Route::get('/dashboard', [EditorController::class, 'dashboard'])->name('dashboard');
        Route::get('/calendar', [EditorController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [EditorController::class, 'profile'])->name('profile');
    });

    // --- B. USER MANAGEMENT ROUTES (Requires 'auth' + 'role.usermanagement') ---
    // Applies the prefix 'usermanagement/' and the route name prefix 'usermanagement.'
    Route::middleware('role.usermanagement')->prefix('usermanagement')->name('UserManagement.')->group(function () {
        Route::get('/dashboard', [UserManagementController::class, 'dashboard'])->name('dashboard');
        Route::get('/calendar', [UserManagementController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [UserManagementController::class, 'profile'])->name('profile');
    });

});