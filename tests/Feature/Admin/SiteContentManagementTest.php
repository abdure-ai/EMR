<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\SiteContentEdit;
use App\Models\SiteContent;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_non_manager_cannot_view_site_content_editor(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('admin.site-content.edit'))
            ->assertForbidden();
    }

    public function test_manager_can_update_site_content(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(SiteContentEdit::class)
            ->set('hero_headline', 'New Hero Headline')
            ->set('contact_email', 'clinic@example.com')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New Hero Headline', SiteContent::current()->hero_headline);
        $this->assertSame('clinic@example.com', SiteContent::current()->contact_email);
    }

    public function test_site_content_edits_are_reflected_on_the_public_site(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(SiteContentEdit::class)
            ->set('hero_headline', 'Unique Hero Headline For Test')
            ->call('save');

        $this->get(route('site.home'))->assertSee('Unique Hero Headline For Test');
    }

    public function test_invalid_contact_email_is_rejected(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(SiteContentEdit::class)
            ->set('contact_email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['contact_email']);
    }
}
