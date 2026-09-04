<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\ContactMessageIndex;
use App\Models\ContactMessage;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactMessageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeManager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        return $manager;
    }

    public function test_non_manager_cannot_view_messages(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('admin.contact-messages.index'))
            ->assertForbidden();
    }

    public function test_manager_can_mark_a_message_read(): void
    {
        $manager = $this->makeManager();
        $message = ContactMessage::create([
            'name' => 'Amina Yusuf', 'email' => 'amina@example.com', 'message' => 'Hello.',
        ]);

        $this->actingAs($manager);

        Livewire::test(ContactMessageIndex::class)->call('markRead', $message->id);

        $this->assertTrue($message->fresh()->is_read);
    }

    public function test_manager_can_delete_a_message(): void
    {
        $manager = $this->makeManager();
        $message = ContactMessage::create([
            'name' => 'Amina Yusuf', 'email' => 'amina@example.com', 'message' => 'Hello.',
        ]);

        $this->actingAs($manager);

        Livewire::test(ContactMessageIndex::class)->call('delete', $message->id);

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    public function test_index_filters_by_read_status(): void
    {
        $manager = $this->makeManager();
        ContactMessage::create(['name' => 'Read One', 'email' => 'a@example.com', 'message' => 'Hi.', 'is_read' => true]);
        ContactMessage::create(['name' => 'Unread One', 'email' => 'b@example.com', 'message' => 'Hi.', 'is_read' => false]);

        $this->actingAs($manager);

        Livewire::test(ContactMessageIndex::class)
            ->set('status', 'unread')
            ->assertSee('Unread One')
            ->assertDontSee('Read One');
    }
}
