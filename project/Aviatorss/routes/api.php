<?php

use App\Http\Controllers\Api\MaxBotSubscriptionController;
use App\Http\Middleware\EnsureMaxBotApiKey;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureMaxBotApiKey::class])->prefix('bot')->group(function () {
    Route::get('/sports', [MaxBotSubscriptionController::class, 'sports']);
    Route::get('/subscriptions/{max_user_id}', [MaxBotSubscriptionController::class, 'show']);
    Route::put('/subscriptions', [MaxBotSubscriptionController::class, 'upsert']);
});
