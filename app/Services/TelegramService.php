<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Telegram Bot API. Every public method fails soft
 * (returns false, never throws) so callers - especially the fan-out job that
 * notifies several practitioners in a loop - can treat one bad chat id or a
 * momentary network error as "skip and continue" rather than an exception to
 * handle at every call site.
 */
class TelegramService
{
    public function isConfigured(): bool
    {
        return filled(config('services.telegram.bot_token'));
    }

    public function sendMessage(string $chatId, string $text): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post($this->apiUrl('sendMessage'), [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if ($response->failed()) {
                Log::warning('Telegram: sendMessage failed', [
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram: sendMessage threw', ['chat_id' => $chatId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function setWebhook(string $url, string $secretToken): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post($this->apiUrl('setWebhook'), [
                'url' => $url,
                'secret_token' => $secretToken,
                'drop_pending_updates' => true,
            ]);

            if ($response->failed()) {
                Log::warning('Telegram: setWebhook failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram: setWebhook threw', ['error' => $e->getMessage()]);

            return false;
        }
    }

    protected function apiUrl(string $method): string
    {
        return sprintf('https://api.telegram.org/bot%s/%s', config('services.telegram.bot_token'), $method);
    }
}
