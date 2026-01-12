<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/email-config', [EmailController::class, 'index']);
    Route::post('/email-config', [EmailController::class, 'store']);
    Route::put('/email-config/{id}', [EmailController::class, 'update']);

    // User Management
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store']);
    
    // Email Logs
    Route::get('/email-logs', [\App\Http\Controllers\EmailLogController::class, 'index']);
    Route::delete('/email-logs/{id}', [\App\Http\Controllers\EmailLogController::class, 'destroy']);
    Route::post('/email-logs/bulk-delete', [\App\Http\Controllers\EmailLogController::class, 'bulkDestroy']);
});

Route::post('/send-email', [EmailController::class, 'sendEmail']);
