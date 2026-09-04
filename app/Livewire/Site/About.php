<?php

namespace App\Livewire\Site;

use App\Models\SiteContent;
use Livewire\Component;

class About extends Component
{
    public function render()
    {
        return view('livewire.site.about', [
            'content' => SiteContent::current(),
        ])->extends('layouts.public')->title('About Us');
    }
}
