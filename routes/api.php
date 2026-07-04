<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\BorrowingApiController;
use App\Http\Controllers\BigDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API
Route::post('/login', [AuthController::class, 'login']);

// Protected API (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('products', ProductApiController::class);
    Route::apiResource('borrowings', BorrowingApiController::class)->except(['update', 'destroy']);
    Route::post('/borrowings/{borrowing}/return', [BorrowingApiController::class, 'returnItems']);

    // Big Data API
    Route::get('/events/stream', [BigDataController::class, 'eventStream']);
    Route::get('/bigdata/stats', [BigDataController::class, 'getStats']);
});
