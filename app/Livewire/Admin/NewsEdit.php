<?php

namespace App\Livewire\Admin;

use App\Models\NewsPost;
use Livewire\Component;
use Livewire\WithFileUploads;

class NewsEdit extends Component
{
    use WithFileUploads;

    public NewsPost $post;

    public string $title = '';

    public string $excerpt = '';

    public string $body = '';

    public $image;

    public bool $is_published = false;

    public function mount(NewsPost $post): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        $this->post = $post;
        $this->title = $post->title;
        $this->excerpt = (string) $post->excerpt;
        $this->body = $post->body;
        $this->is_published = $post->is_published;
    }

    public function removeImage(): void
    {
        $this->post->update(['image_path' => null]);
        session()->flash('status', 'Image removed.');
    }

    public function save()
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_published' => ['boolean'],
        ]);

        unset($validated['image']);

        if ($this->image) {
            $validated['image_path'] = $this->image->store('news', 'public');
        }

        if ($this->is_published && ! $this->post->is_published) {
            $validated['published_at'] = $this->post->published_at ?? now();
        }

        $this->post->update($validated);

        session()->flash('status', 'Post updated.');
    }

    public function render()
    {
        return view('livewire.admin.news-edit')
            ->extends('layouts.app')->title('Edit Post');
    }
}
