<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Telegram webhook (alternative route)
Route::post('/telegram/webhook/{token}', function ($token) {
    if ($token !== config('services.telegram.webhook_secret')) {
        abort(403);
    }
    return app(\App\Http\Controllers\Api\TelegramController::class)->webhook(request());
});
