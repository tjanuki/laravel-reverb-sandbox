<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\RoomController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Rooms
    Route::resource('rooms', RoomController::class);
    Route::post('/rooms/{room}/messages', [MessageController::class, 'store'])
        ->name('rooms.messages.store');

    // Room membership
    Route::post('/rooms/{room}/join', [RoomController::class, 'join'])
        ->name('rooms.join');
    Route::delete('/rooms/{room}/leave', [RoomController::class, 'leave'])
        ->name('rooms.leave');
});
