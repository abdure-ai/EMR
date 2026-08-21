<?php

namespace App\Livewire\Medications;

use App\Models\Medication;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MedicationEdit extends Component
{
    public Medication $medication;

    public string $name = '';

    public string $form = '';

    public string $strength = '';

    public string $price = '';

    public function mount(Medication $medication): void
    {
        Gate::authorize('update', $medication);

        $this->medication = $medication;
        $this->name = $medication->name;
        $this->form = (string) $medication->form;
        $this->strength = (string) $medication->strength;
        $this->price = $medication->price !== null ? (string) $medication->price : '';
    }

    public function save()
    {
        Gate::authorize('update', $this->medication);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'form' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->medication->update([
            ...$validated,
            'price' => $validated['price'] !== '' ? $validated['price'] : null,
        ]);

        session()->flash('status', "Medication \"{$this->medication->name}\" was updated.");

        return $this->redirect(route('medications.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.medications.medication-edit')
            ->extends('layouts.app')->title('Edit Medication');
    }
}
