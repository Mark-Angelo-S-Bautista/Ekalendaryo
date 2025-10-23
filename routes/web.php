<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserMan\UserManagementController;
use App\Http\Controllers\Editor\EditorController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Viewer\ViewerController;
use Illuminate\Support\Facades\Route;

// Group the routes without a 'prefix' so they start at the root URL (/)

Route::get('/', [LoginController::class, 'index'])->name('Auth.login');
Route::post('/', [LoginController::class, 'store'])->name('login');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::post('/password-reset', [PasswordResetController::class, 'sendResetLink'])
    ->name('password.reset.request');
// Route::get('/passwordreset', [PasswordResetController::class,'index'])->name('passwordreset');
// Route::post('/passwordreset', [PasswordResetController::class,'passwordReset'])->name('passwordReset');

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

    // --- C. VIEWER ROUTES (Requires 'auth' + 'role.viewer') ---
    // Applies the prefix 'usermanagement/' and the route name prefix 'usermanagement.'
    Route::middleware('role.viewer')->prefix('viewer')->name('Viewer.')->group(function () {
        Route::get('/dashboard', [ViewerController::class, 'dashboard'])->name('dashboard');
        Route::get('/calendar', [ViewerController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [ViewerController::class, 'profile'])->name('profile');
    });

});