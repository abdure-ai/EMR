<?php

namespace App\Livewire\Services;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ServiceEdit extends Component
{
    public Service $service;

    public string $department_id = '';

    public string $name = '';

    public string $duration_minutes = '';

    public string $price = '';

    public function mount(Service $service): void
    {
        Gate::authorize('update', $service);

        $this->service = $service;
        $this->department_id = (string) $service->department_id;
        $this->name = $service->name;
        $this->duration_minutes = (string) $service->duration_minutes;
        $this->price = $service->price !== null ? (string) $service->price : '';
    }

    public function save()
    {
        Gate::authorize('update', $this->service);

        $validated = $this->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->service->update([
            ...$validated,
            'price' => $validated['price'] !== '' ? $validated['price'] : null,
        ]);

        session()->flash('status', "Service \"{$this->service->name}\" was updated.");

        return $this->redirect(route('services.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.services.service-edit', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ])->extends('layouts.app')->title('Edit Service');
    }
}
