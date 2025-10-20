<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserMan\UserManagementController;
use App\Http\Controllers\Editor\EditorController;
use Illuminate\Support\Facades\Route;

// Group the routes without a 'prefix' so they start at the root URL (/)

Route::get('/', [LoginController::class, 'index'])->name('Auth.login');

Route::prefix('Editor')->group(function () {
    Route::get('/dashboard', [EditorController::class, 'dashboard'])->name('Editor.dashboard');
    Route::get('/calendar', [EditorController::class, 'calendar'])->name('Editor.calendar');
    Route::get('/profile', [EditorController::class, 'profile'])->name('Editor.profile');
});

Route::prefix('UserManagement')->group(function () {
    Route::get('/dashboard', [UserManagementController::class, 'dashboard'])->name('UserManagement.dashboard');
    Route::get('/calendar', [UserManagementController::class, 'calendar'])->name('UserManagement.calendar');
    Route::get('/profile', [UserManagementController::class, 'profile'])->name('UserManagement.profile');
});