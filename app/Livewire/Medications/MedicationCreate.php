<?php

namespace App\Livewire\Medications;

use App\Models\Medication;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MedicationCreate extends Component
{
    public string $name = '';

    public string $form = '';

    public string $strength = '';

    public string $price = '';

    public function mount(): void
    {
        Gate::authorize('create', Medication::class);
    }

    public function save()
    {
        Gate::authorize('create', Medication::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'form' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        Medication::create([
            ...$validated,
            'price' => $validated['price'] !== '' ? $validated['price'] : null,
        ]);

        session()->flash('status', "Medication \"{$validated['name']}\" was created.");

        return $this->redirect(route('medications.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.medications.medication-create')
            ->extends('layouts.app')->title('New Medication');
    }
}
