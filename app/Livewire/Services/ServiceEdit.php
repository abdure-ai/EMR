<?php

namespace App\Livewire\Services;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ServiceEdit extends Component
{
    use WithFileUploads;

    public Service $service;

    public string $department_id = '';

    public string $name = '';

    public string $duration_minutes = '';

    public string $price = '';

    public string $description = '';

    public $image;

    public bool $show_on_website = false;

    public function mount(Service $service): void
    {
        Gate::authorize('update', $service);

        $this->service = $service;
        $this->department_id = (string) $service->department_id;
        $this->name = $service->name;
        $this->duration_minutes = (string) $service->duration_minutes;
        $this->price = $service->price !== null ? (string) $service->price : '';
        $this->description = (string) $service->description;
        $this->show_on_website = (bool) $service->show_on_website;
    }

    public function removeImage(): void
    {
        Gate::authorize('update', $this->service);

        $this->service->update(['image_path' => null]);
        session()->flash('status', 'Image removed.');
    }

    public function save()
    {
        Gate::authorize('update', $this->service);

        $validated = $this->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'show_on_website' => ['boolean'],
        ]);

        unset($validated['image']);

        if ($this->image) {
            $validated['image_path'] = $this->image->store('services', 'public');
        }

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
