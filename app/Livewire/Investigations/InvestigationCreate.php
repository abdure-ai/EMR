<?php

namespace App\Livewire\Investigations;

use App\Models\Investigation;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InvestigationCreate extends Component
{
    public string $category = '';

    public string $subcategory = '';

    public string $name = '';

    public string $price = '';

    public function mount(): void
    {
        Gate::authorize('create', Investigation::class);
    }

    public function save()
    {
        Gate::authorize('create', Investigation::class);

        $validated = $this->validate([
            'category' => ['required', 'in:lab,imaging,procedure'],
            'subcategory' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        Investigation::create([
            ...$validated,
            'price' => $validated['price'] !== '' ? $validated['price'] : null,
        ]);

        session()->flash('status', "Investigation \"{$validated['name']}\" was created.");

        return $this->redirect(route('investigations.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.investigations.investigation-create')
            ->extends('layouts.app')->title('New Investigation');
    }
}
