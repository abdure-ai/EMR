<?php

namespace Tests\Feature\Site;

use App\Livewire\Site\Contact;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\NewsPost;
use App\Models\Service;
use App\Models\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_editable_hero_content(): void
    {
        SiteContent::current()->update(['hero_headline' => 'Healing Rooted in Tradition']);

        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee('Healing Rooted in Tradition');
    }

    public function test_about_page_loads(): void
    {
        $this->get(route('site.about'))->assertOk();
    }

    public function test_services_page_only_shows_services_marked_for_the_website(): void
    {
        $department = Department::create(['name' => 'General Medicine']);

        Service::create([
            'department_id' => $department->id, 'name' => 'Herbal Therapy Session',
            'duration_minutes' => 60, 'show_on_website' => true, 'is_active' => true,
        ]);
        Service::create([
            'department_id' => $department->id, 'name' => 'Internal Only Consultation',
            'duration_minutes' => 30, 'show_on_website' => false, 'is_active' => true,
        ]);

        $this->get(route('site.services'))
            ->assertOk()
            ->assertSee('Herbal Therapy Session')
            ->assertDontSee('Internal Only Consultation');
    }

    public function test_contact_form_creates_a_message_and_shows_a_confirmation(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Amina Yusuf')
            ->set('email', 'amina@example.com')
            ->set('message', 'I would like to book a consultation.')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Amina Yusuf', 'email' => 'amina@example.com',
        ]);
    }

    public function test_contact_form_requires_name_email_and_message(): void
    {
        Livewire::test(Contact::class)
            ->set('name', '')
            ->set('email', '')
            ->set('message', '')
            ->call('send')
            ->assertHasErrors(['name', 'email', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_news_index_only_shows_published_posts(): void
    {
        NewsPost::create([
            'title' => 'Clinic Now Open', 'body' => 'Body text.',
            'is_published' => true, 'published_at' => now()->subDay(),
        ]);
        NewsPost::create([
            'title' => 'Draft Announcement', 'body' => 'Body text.',
            'is_published' => false,
        ]);

        $this->get(route('site.news.index'))
            ->assertOk()
            ->assertSee('Clinic Now Open')
            ->assertDontSee('Draft Announcement');
    }

    public function test_news_show_renders_a_published_post(): void
    {
        $post = NewsPost::create([
            'title' => 'Clinic Now Open', 'body' => "First paragraph.\n\nSecond paragraph.",
            'is_published' => true, 'published_at' => now()->subDay(),
        ]);

        $this->get(route('site.news.show', $post))
            ->assertOk()
            ->assertSee('Clinic Now Open')
            ->assertSee('First paragraph.');
    }

    public function test_news_show_404s_for_an_unpublished_post(): void
    {
        $post = NewsPost::create([
            'title' => 'Draft Announcement', 'body' => 'Body text.', 'is_published' => false,
        ]);

        $this->get(route('site.news.show', $post))->assertNotFound();
    }

    public function test_news_show_404s_for_a_future_dated_post(): void
    {
        $post = NewsPost::create([
            'title' => 'Scheduled Announcement', 'body' => 'Body text.',
            'is_published' => true, 'published_at' => now()->addWeek(),
        ]);

        $this->get(route('site.news.show', $post))->assertNotFound();
    }
}
