<?php

namespace App\Livewire\Site;

use App\Models\NewsPost;
use App\Models\Service;
use App\Models\SiteContent;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.site.home', [
            'content' => SiteContent::current(),
            'services' => Service::shownOnWebsite()->orderBy('name')->limit(3)->get(),
            'posts' => NewsPost::published()->latest('published_at')->limit(3)->get(),
        ])->extends('layouts.public')->title('Home');
    }
}
