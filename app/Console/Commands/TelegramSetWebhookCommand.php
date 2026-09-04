<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('telegram:set-webhook')]
#[Description("Registers this app's /telegram/webhook URL with Telegram as the bot's webhook.")]
class TelegramSetWebhookCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN is not set in .env.');

            return self::FAILURE;
        }

        if (! config('services.telegram.webhook_secret')) {
            $this->error('TELEGRAM_WEBHOOK_SECRET is not set in .env.');

            return self::FAILURE;
        }

        $url = route('telegram.webhook');

        if (! str_starts_with($url, 'https://')) {
            $this->warn("Webhook URL is not HTTPS ({$url}) - Telegram requires HTTPS. Use ngrok locally, or set APP_URL to your production HTTPS domain.");
        }

        $ok = $telegram->setWebhook($url, config('services.telegram.webhook_secret'));

        if ($ok) {
            $this->info("Webhook registered: {$url}");
        } else {
            $this->error('Failed to register webhook - check the logs.');
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
