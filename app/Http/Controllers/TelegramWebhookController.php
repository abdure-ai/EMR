<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives Telegram's webhook callbacks. Handles exactly one flow: a
 * practitioner tapping the "Open in Telegram" deep link generated on their
 * profile page, which sends the bot "/start {code}" - we match that code
 * back to a User and store their chat id.
 *
 * Always responds 200, even on rejected requests - Telegram retries (and
 * eventually disables) a webhook that doesn't return 2xx, and none of our
 * rejection reasons (bad secret, unknown code, unrelated message) are
 * transport failures that should trigger a retry.
 */
class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramService $telegram): JsonResponse
    {
        if ($request->header('X-Telegram-Bot-Api-Secret-Token') !== config('services.telegram.webhook_secret')) {
            return response()->json(['ok' => false]);
        }

        $text = (string) $request->input('message.text', '');
        $chatId = (string) $request->input('message.chat.id', '');

        if ($chatId === '' || ! str_starts_with($text, '/start')) {
            return response()->json(['ok' => true]);
        }

        // Telegram sends a bare "/start" (no trailing space, no payload) when
        // someone opens the bot directly and taps its built-in Start button,
        // rather than following the profile page's "?start={code}" deep
        // link. That's a normal, common way to first find the bot - greet
        // them instead of staying silent.
        $code = trim(substr($text, 6));

        if ($code === '') {
            $telegram->sendMessage($chatId, 'Hi! To link your account, open your NESIHA profile page and tap "Connect Telegram" - it will bring you back here with a link code.');

            return response()->json(['ok' => true]);
        }

        $user = User::where('telegram_link_code', $code)->first();

        if (! $user) {
            $telegram->sendMessage($chatId, 'This link code is invalid or has expired. Generate a new one from your NESIHA profile page.');

            return response()->json(['ok' => true]);
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_link_code' => null,
        ]);

        $telegram->sendMessage($chatId, "✅ Your Telegram is now linked to NESIHA as <b>{$user->name}</b>. You'll get new-patient notifications here.");

        return response()->json(['ok' => true]);
    }
}
