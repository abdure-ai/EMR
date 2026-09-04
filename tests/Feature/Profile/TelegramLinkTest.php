<?php

namespace Tests\Feature\Profile;

use App\Livewire\Profile\TelegramLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TelegramLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_connect_generates_a_link_code_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(TelegramLink::class)
            ->call('connect');

        $this->assertNotNull($user->fresh()->telegram_link_code);
    }

    public function test_disconnect_clears_the_link_for_an_already_linked_user(): void
    {
        $user = User::factory()->create([
            'telegram_chat_id' => '555',
            'telegram_link_code' => null,
        ]);
        $this->actingAs($user);

        Livewire::test(TelegramLink::class)
            ->call('disconnect');

        $user->refresh();
        $this->assertNull($user->telegram_chat_id);
        $this->assertNull($user->telegram_link_code);
    }

    public function test_profile_page_renders_the_telegram_card(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('profile'))
            ->assertOk()
            ->assertSeeLivewire('profile.telegram-link');
    }
}
