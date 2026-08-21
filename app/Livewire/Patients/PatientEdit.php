<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PatientEdit extends Component
{
    public Patient $patient;

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

    public function mount(Patient $patient): void
    {
        Gate::authorize('update', $patient);

        $this->patient = $patient;
        $this->first_name = $patient->first_name;
        $this->middle_name = (string) $patient->middle_name;
        $this->last_name = $patient->last_name;
        $this->sex = $patient->sex;
        $this->age = $patient->date_of_birth?->age ?? (string) $patient->age;
        $this->phone = $patient->phone;
        $this->region = (string) $patient->region;
        $this->zone = (string) $patient->zone;
        $this->woreda = (string) $patient->woreda;
        $this->kebele = (string) $patient->kebele;
        $this->house_no = (string) $patient->house_no;
    }

    public function save()
    {
        Gate::authorize('update', $this->patient);

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
        ]);

        $this->patient->update($validated);

        session()->flash('status', "Patient {$this->patient->patient_id} was updated.");

        return $this->redirect(route('patients.show', $this->patient), navigate: true);
    }

    public function render()
    {
        return view('livewire.patients.patient-edit')
            ->extends('layouts.app')->title('Edit Patient');
    }
}
