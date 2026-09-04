<?php

namespace App\Livewire\Services;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ServiceCreate extends Component
{
    use WithFileUploads;

    public string $department_id = '';

    public string $name = '';

    public string $duration_minutes = '30';

    public string $price = '';

    public string $description = '';

    public $image;

    public bool $show_on_website = false;

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
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'show_on_website' => ['boolean'],
        ]);

        unset($validated['image']);

        if ($this->image) {
            $validated['image_path'] = $this->image->store('services', 'public');
        }

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
