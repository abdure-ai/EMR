<?php

namespace App\Livewire\Admin;

use App\Models\NewsPost;
use Livewire\Component;
use Livewire\WithFileUploads;

class NewsCreate extends Component
{
    use WithFileUploads;

    public string $title = '';

    public string $excerpt = '';

    public string $body = '';

    public $image;

    public bool $is_published = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);
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

        $validated['published_at'] = $this->is_published ? now() : null;
        $validated['created_by'] = auth()->id();

        $post = NewsPost::create($validated);

        session()->flash('status', "\"{$post->title}\" was created.");

        return $this->redirect(route('admin.news.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.news-create')
            ->extends('layouts.app')->title('New Post');
    }
}
