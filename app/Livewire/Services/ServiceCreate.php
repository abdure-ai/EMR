<?php

namespace App\Livewire\Services;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ServiceCreate extends Component
{
    public string $department_id = '';

    public string $name = '';

    public string $duration_minutes = '30';

    public string $price = '';

    public function mount(): void
    {
        Gate::authorize('create', Service::class);
    }

    public function save()
    {
        Gate::authorize('create', Service::class);

        $validated = $this->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        Service::create([
            ...$validated,
            'price' => $validated['price'] !== '' ? $validated['price'] : null,
        ]);

        session()->flash('status', "Service \"{$validated['name']}\" was created.");

        return $this->redirect(route('services.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.services.service-create', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ])->extends('layouts.app')->title('New Service');
    }
}
