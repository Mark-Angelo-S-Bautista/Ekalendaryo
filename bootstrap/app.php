<?php


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EditorRole;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\UserManagementRole;
use App\Http\Middleware\ViewerRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            // ADD YOUR CUSTOM ROLE MIDDLEWARE HERE
            'role.editor' => EditorRole::class, 
            'role.usermanagement' => UserManagementRole::class,
            'role.viewer' => ViewerRole::class,
            'backhistory' => PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
