<?php

use Illuminate\Support\Facades\Route;

// Login Route
Route::get('/', function () {
    return view('login');
})->name('login');

// Dashboard Routes
Route::prefix('dashboard')->group(function () {
    Route::get('/', function () {
        return view('dashboard.configs');
    })->name('dashboard.configs');

    Route::get('/test-email', function () {
        return view('dashboard.test-email');
    })->name('dashboard.test-email');

    Route::get('/logs', function () {
        return view('dashboard.logs');
    })->name('dashboard.logs');

    Route::get('/docs', function () {
        return view('dashboard.api-docs');
    })->name('dashboard.api-docs');

    Route::get('/users', function () {
        return view('dashboard.users');
    })->name('dashboard.users');
});
