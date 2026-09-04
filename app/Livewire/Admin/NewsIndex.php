<?php

namespace App\Livewire\Admin;

use App\Models\NewsPost;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class NewsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status');
        $this->resetPage();
    }

    public function togglePublished(int $postId): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        $post = NewsPost::findOrFail($postId);
        $post->update([
            'is_published' => ! $post->is_published,
            'published_at' => ! $post->is_published ? ($post->published_at ?? now()) : $post->published_at,
        ]);
    }

    public function delete(int $postId): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        NewsPost::findOrFail($postId)->delete();

        session()->flash('status', 'Post deleted.');
    }

    public function render()
    {
        return view('livewire.admin.news-index', [
            'posts' => NewsPost::query()
                ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->when($this->status === 'published', fn ($q) => $q->where('is_published', true))
                ->when($this->status === 'draft', fn ($q) => $q->where('is_published', false))
                ->latest('id')
                ->paginate(15),
        ])->extends('layouts.app')->title('News');
    }
}
