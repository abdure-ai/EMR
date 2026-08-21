<?php

namespace App\Livewire\Settings;

use App\Models\ClinicSetting;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ClinicSettingsEdit extends Component
{
    public string $new_patient_card_fee = '';

    public string $revisit_free_within_days = '';

    public string $expiry_alert_days = '';

    public function mount(): void
    {
        $settings = ClinicSetting::current();

        Gate::authorize('view', $settings);

        $this->new_patient_card_fee = (string) $settings->new_patient_card_fee;
        $this->revisit_free_within_days = (string) $settings->revisit_free_within_days;
        $this->expiry_alert_days = (string) $settings->expiry_alert_days;
    }

    public function save()
    {
        $settings = ClinicSetting::current();

        Gate::authorize('update', $settings);

        $validated = $this->validate([
            'new_patient_card_fee' => ['required', 'numeric', 'min:0'],
            'revisit_free_within_days' => ['required', 'integer', 'min:0', 'max:365'],
            'expiry_alert_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $settings->update($validated);

        session()->flash('status', 'Settings updated.');
    }

    public function render()
    {
        return view('livewire.settings.clinic-settings-edit')
            ->extends('layouts.app')->title('Settings');
    }
}
