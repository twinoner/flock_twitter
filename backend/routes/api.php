<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);

// Default auth route (keep this)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
