<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/ping', function () {
    return response()->json('pong');
});


Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('auth:sanctum')
        ->name('verification.send');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
