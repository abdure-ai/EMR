<?php

namespace App\Livewire\Site;

use App\Models\Service;
use Livewire\Component;

class Services extends Component
{
    public function render()
    {
        return view('livewire.site.services', [
            'services' => Service::shownOnWebsite()->with('department')->orderBy('name')->get(),
        ])->extends('layouts.public')->title('Services');
    }
}
