<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class DepartmentCreate extends Component
{
    public string $name = '';

    public function mount(): void
    {
        Gate::authorize('create', Department::class);
    }

    public function save()
    {
        Gate::authorize('create', Department::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
        ]);

        Department::create($validated);

        session()->flash('status', "Department \"{$validated['name']}\" was created.");

        return $this->redirect(route('departments.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.departments.department-create')
            ->extends('layouts.app')->title('New Department');
    }
}
