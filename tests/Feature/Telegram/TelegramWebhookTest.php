<?php

namespace Tests\Feature\Telegram;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.webhook_secret' => 'shh-its-a-secret',
        ]);
    }

    protected function postUpdate(array $message, ?string $secret = 'shh-its-a-secret')
    {
        return $this->postJson(
            route('telegram.webhook'),
            ['update_id' => 1, 'message' => $message],
            $secret !== null ? ['X-Telegram-Bot-Api-Secret-Token' => $secret] : []
        );
    }

    public function test_a_request_with_the_wrong_secret_is_rejected_but_still_returns_200(): void
    {
        $user = User::factory()->create(['telegram_link_code' => 'abc123']);

        $response = $this->postUpdate(
            ['chat' => ['id' => 555], 'text' => '/start abc123'],
            secret: 'wrong-secret'
        );

        $response->assertOk()->assertJson(['ok' => false]);

        $this->assertNull($user->fresh()->telegram_chat_id);
    }

    public function test_a_valid_start_command_links_the_matching_user(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $user = User::factory()->create(['telegram_link_code' => 'abc123']);

        $response = $this->postUpdate(['chat' => ['id' => 555], 'text' => '/start abc123']);

        $response->assertOk()->assertJson(['ok' => true]);

        $user->refresh();
        $this->assertSame('555', $user->telegram_chat_id);
        $this->assertNull($user->telegram_link_code);

        Http::assertSent(fn ($request) => $request['chat_id'] === 555 || $request['chat_id'] === '555');
    }

    public function test_an_unknown_code_does_not_link_anyone(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $response = $this->postUpdate(['chat' => ['id' => 555], 'text' => '/start does-not-exist']);

        $response->assertOk()->assertJson(['ok' => true]);

        Http::assertSentCount(1);
    }

    public function test_a_non_start_message_is_a_no_op(): void
    {
        Http::fake();

        $response = $this->postUpdate(['chat' => ['id' => 555], 'text' => 'hello there']);

        $response->assertOk()->assertJson(['ok' => true]);

        Http::assertNothingSent();
    }

    public function test_a_bare_start_with_no_payload_gets_a_greeting_instead_of_silence(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $response = $this->postUpdate(['chat' => ['id' => 555], 'text' => '/start']);

        $response->assertOk()->assertJson(['ok' => true]);

        Http::assertSent(fn ($request) => ($request['chat_id'] === 555 || $request['chat_id'] === '555')
            && str_contains($request['text'], 'NESIHA profile page'));
    }
}
