<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\NewsCreate;
use App\Livewire\Admin\NewsEdit;
use App\Livewire\Admin\NewsIndex;
use App\Models\NewsPost;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsManagementTest extends TestCase
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

    public function test_non_manager_cannot_manage_news(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('admin.news.index'))
            ->assertForbidden();
    }

    public function test_manager_can_create_a_published_post_with_a_unique_slug(): void
    {
        $manager = $this->makeManager();
        $this->actingAs($manager);

        Livewire::test(NewsCreate::class)
            ->set('title', 'Clinic Now Open')
            ->set('body', 'Announcement body.')
            ->set('is_published', true)
            ->call('save')
            ->assertHasNoErrors();

        $post = NewsPost::first();
        $this->assertSame('Clinic Now Open', $post->title);
        $this->assertSame('clinic-now-open', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_duplicate_titles_get_a_unique_slug(): void
    {
        $manager = $this->makeManager();
        $this->actingAs($manager);

        NewsPost::create(['title' => 'Clinic Now Open', 'body' => 'First.', 'is_published' => true]);

        Livewire::test(NewsCreate::class)
            ->set('title', 'Clinic Now Open')
            ->set('body', 'Second.')
            ->call('save')
            ->assertHasNoErrors();

        $slugs = NewsPost::orderBy('id')->pluck('slug');
        $this->assertSame(2, $slugs->count());
        $this->assertNotSame($slugs->first(), $slugs->last());
    }

    public function test_manager_can_edit_and_unpublish_a_post(): void
    {
        $manager = $this->makeManager();
        $post = NewsPost::create(['title' => 'Original', 'body' => 'Body.', 'is_published' => true, 'published_at' => now()]);

        $this->actingAs($manager);

        Livewire::test(NewsEdit::class, ['post' => $post])
            ->set('title', 'Updated Title')
            ->set('is_published', false)
            ->call('save')
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertSame('Updated Title', $post->title);
        $this->assertFalse($post->is_published);
    }

    public function test_manager_can_delete_a_post(): void
    {
        $manager = $this->makeManager();
        $post = NewsPost::create(['title' => 'To Delete', 'body' => 'Body.']);

        $this->actingAs($manager);

        Livewire::test(NewsIndex::class)->call('delete', $post->id);

        $this->assertDatabaseMissing('news_posts', ['id' => $post->id]);
    }

    public function test_index_filters_by_status(): void
    {
        $manager = $this->makeManager();
        NewsPost::create(['title' => 'Published Post', 'body' => 'Body.', 'is_published' => true, 'published_at' => now()]);
        NewsPost::create(['title' => 'Draft Post', 'body' => 'Body.', 'is_published' => false]);

        $this->actingAs($manager);

        Livewire::test(NewsIndex::class)
            ->set('status', 'published')
            ->assertSee('Published Post')
            ->assertDontSee('Draft Post');
    }
}
