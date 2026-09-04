<?php

namespace App\Livewire\Site;

use App\Models\NewsPost;
use Livewire\Component;

class NewsShow extends Component
{
    public NewsPost $post;

    public function mount(NewsPost $post): void
    {
        abort_unless($post->is_published && (! $post->published_at || $post->published_at->isPast()), 404);

        $this->post = $post;
    }

    public function render()
    {
        return view('livewire.site.news-show', [
            'recent' => NewsPost::published()->where('id', '!=', $this->post->id)->latest('published_at')->limit(3)->get(),
        ])->extends('layouts.public')->title($this->post->title);
    }
}
