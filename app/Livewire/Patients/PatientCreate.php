<?php

namespace App\Livewire\Patients;

use App\Models\Department;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\CheckInService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PatientCreate extends Component
{
    public string $first_name = '';

    public string $middle_name = '';

    public string $last_name = '';

    public string $sex = '';

    public string $age = '';

    public string $phone = '';

    public string $region = '';

    public string $zone = '';

    public string $woreda = '';

    public string $kebele = '';

    public string $house_no = '';

    public string $department_id = '';

    public string $service_id = '';

    public string $practitioner_id = '';

    public function mount(): void
    {
        Gate::authorize('create', Patient::class);
    }

    public function updatedDepartmentId(): void
    {
        $this->service_id = '';
    }

    public function getServicesProperty()
    {
        if (! $this->department_id) {
            return collect();
        }

        return Service::where('department_id', $this->department_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function save()
    {
        Gate::authorize('create', Patient::class);

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'sex' => ['required', 'in:male,female'],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'region' => ['nullable', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'woreda' => ['nullable', 'string', 'max:255'],
            'kebele' => ['nullable', 'string', 'max:255'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'service_id' => ['required', 'exists:services,id'],
            'practitioner_id' => ['required', 'exists:users,id'],
        ]);

        $patient = Patient::create([
            ...collect($validated)->except(['department_id', 'service_id', 'practitioner_id'])->all(),
            'created_by' => auth()->id(),
        ]);

        $checkIn = app(CheckInService::class)->registerAndCheckIn(
            $patient,
            User::findOrFail($validated['practitioner_id']),
            Service::findOrFail($validated['service_id']),
            auth()->user(),
        );

        $summary = $checkIn['invoice']
            ? "registration and visit fee sent to the cashier (invoice {$checkIn['invoice']->invoice_number})"
            : 'nothing due - added straight to the queue';

        session()->flash('status', "Patient {$patient->patient_id} registered - {$summary}.");

        return $this->redirect(route('patients.show', $patient), navigate: true);
    }

    public function render()
    {
        return view('livewire.patients.patient-create', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'practitioners' => User::role('Practitioner')->orderBy('name')->get(),
        ])->extends('layouts.app')->title('Register Patient');
    }
}
