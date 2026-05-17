<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TweetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::get('/timeline',              [TimelineController::class, 'index']);
    Route::post('/tweets',               [TweetController::class, 'store']);
    Route::delete('/tweets/{tweet}',     [TweetController::class, 'destroy']);

    Route::post('/users/{user}/follow',  [FollowController::class, 'store']);
    Route::delete('/users/{user}/follow',[FollowController::class, 'destroy']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
