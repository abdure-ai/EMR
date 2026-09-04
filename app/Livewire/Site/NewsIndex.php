<?php

namespace App\Livewire\Site;

use App\Models\NewsPost;
use Livewire\Component;
use Livewire\WithPagination;

class NewsIndex extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.site.news-index', [
            'posts' => NewsPost::published()->orderByDesc('published_at')->paginate(9),
        ])->extends('layouts.public')->title('News');
    }
}
