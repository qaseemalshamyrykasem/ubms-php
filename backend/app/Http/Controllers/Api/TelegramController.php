<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function __construct(private TelegramService $service)
    {
    }

    public function status(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'connected' => $user->telegramConnected(),
            'username' => $user->telegram_username,
            'connected_at' => $user->telegram_connected_at,
            'bot_configured' => $this->service->isConfigured(),
            'bot_username' => config('services.telegram.bot_username'),
        ]);
    }

    public function generateCode(Request $request)
    {
        $user = $request->user();
        $code = $this->service->generateVerificationCode($user);
        $botUsername = config('services.telegram.bot_username');
        $deepLink = $botUsername ? "https://t.me/{$botUsername}?start={$code}" : null;

        return response()->json([
            'code' => $code,
            'deep_link' => $deepLink,
            'bot_username' => $botUsername,
            'expires_in_minutes' => 10,
        ]);
    }

    public function disconnect(Request $request)
    {
        $this->service->disconnect($request->user());
        return response()->json(['message' => __('telegram.disconnected')]);
    }

    public function testMessage(Request $request)
    {
        $user = $request->user();
        if (!$user->telegramConnected()) {
            return response()->json(['message' => __('telegram.not_connected')], 422);
        }
        $resp = $this->service->sendMessage($user->telegram_chat_id, '✅ ' . __('telegram.test_message', [], $user->locale ?? 'ar'));
        return response()->json(['success' => (bool) $resp, 'response' => $resp]);
    }

    public function webhook(Request $request)
    {
        $this->service->handleWebhook($request->all());
        return response()->json(['ok' => true]);
    }
}
