<?php

use App\Http\Controllers\Api\V1\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/messages', [MessageController::class, 'store'])->name('api.v1.messages.store');
});
