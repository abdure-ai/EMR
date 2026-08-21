<?php

namespace App\Livewire\Investigations;

use App\Models\Investigation;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InvestigationEdit extends Component
{
    public Investigation $investigation;

    public string $category = '';

    public string $subcategory = '';

    public string $name = '';

    public string $price = '';

    public function mount(Investigation $investigation): void
    {
        Gate::authorize('update', $investigation);

        $this->investigation = $investigation;
        $this->category = $investigation->category;
        $this->subcategory = (string) $investigation->subcategory;
        $this->name = $investigation->name;
        $this->price = $investigation->price !== null ? (string) $investigation->price : '';
    }

    public function save()
    {
        Gate::authorize('update', $this->investigation);

        $validated = $this->validate([
            'category' => ['required', 'in:lab,imaging,procedure'],
            'subcategory' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->investigation->update([
            ...$validated,
            'price' => $validated['price'] !== '' ? $validated['price'] : null,
        ]);

        session()->flash('status', "Investigation \"{$this->investigation->name}\" was updated.");

        return $this->redirect(route('investigations.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.investigations.investigation-edit')
            ->extends('layouts.app')->title('Edit Investigation');
    }
}
