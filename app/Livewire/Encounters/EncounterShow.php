<?php

namespace App\Livewire\Encounters;

use App\Models\Encounter;
use App\Models\Investigation;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedicalInfo;
use App\Models\Prescription;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EncounterShow extends Component
{
    public Patient $patient;

    public Encounter $encounter;

    public string $patient_note = '';

    public array $selectedInvestigations = [];

    public array $prescriptionItems = [];

    public string $newMedicationId = '';

    public string $newCustomName = '';

    public string $newDosage = '';

    public string $newFrequency = '';

    public string $newDuration = '';

    public string $newQuantity = '';

    public string $newInstructions = '';

    public string $results = '';

    public string $follow_up_date = '';

    public string $follow_up_reason = '';

    public ?string $noteSavedAt = null;

    public bool $editingMedical = false;

    public string $main_complaint = '';

    public string $medical_history = '';

    public string $allergies = '';

    public string $current_medications = '';

    public string $previous_treatments = '';

    public function mount(Patient $patient, Encounter $encounter): void
    {
        Gate::authorize('view', $encounter);

        abort_unless($encounter->patient_id === $patient->id, 404);

        $this->patient = $patient;
        $this->encounter = $encounter;

        $this->patient_note = $encounter->patient_note ?? '';
        $this->results = $encounter->results ?? '';
        $this->follow_up_date = $encounter->follow_up_date?->toDateString() ?? '';
        $this->follow_up_reason = $encounter->follow_up_reason ?? '';
        $this->selectedInvestigations = $encounter->investigations()->pluck('investigations.id')->map(fn ($id) => (string) $id)->all();

        $this->prescriptionItems = $encounter->prescription?->items
            ->map(fn ($item) => [
                'medication_id' => $item->medication_id,
                'name' => $item->name,
                'dosage' => $item->dosage,
                'frequency' => $item->frequency,
                'duration' => $item->duration,
                'quantity' => $item->quantity,
                'instructions' => $item->instructions,
            ])->all() ?? [];

        $this->fillMedicalFields();
    }

    protected function fillMedicalFields(): void
    {
        $info = $this->patient->medicalInfo;

        $this->main_complaint = $info?->main_complaint ?? '';
        $this->medical_history = $info?->medical_history ?? '';
        $this->allergies = $info?->allergies ?? '';
        $this->current_medications = $info?->current_medications ?? '';
        $this->previous_treatments = $info?->previous_treatments ?? '';
    }

    protected function rules(): array
    {
        return [
            'patient_note' => ['nullable', 'string', 'max:5000'],
            'results' => ['nullable', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'follow_up_reason' => ['nullable', 'string', 'max:255'],
            'selectedInvestigations' => ['array'],
            'selectedInvestigations.*' => ['exists:investigations,id'],
        ];
    }

    /**
     * Auto-saves the patient note as a draft while the practitioner types -
     * debounced client-side (wire:model.live.debounce) so this fires on
     * pauses, not every keystroke.
     */
    public function updatedPatientNote(): void
    {
        if (! Gate::allows('update', $this->encounter)) {
            return;
        }

        $this->validateOnly('patient_note');

        $this->encounter->update(['patient_note' => $this->patient_note, 'status' => 'draft']);

        $this->noteSavedAt = now()->format('H:i:s');
    }

    public function addPrescriptionItem(): void
    {
        Gate::authorize('update', $this->encounter);

        $validated = $this->validate([
            'newMedicationId' => ['nullable', 'exists:medications,id'],
            'newCustomName' => ['nullable', 'string', 'max:255', 'required_without:newMedicationId'],
            'newDosage' => ['nullable', 'string', 'max:255'],
            'newFrequency' => ['nullable', 'string', 'max:255'],
            'newDuration' => ['nullable', 'string', 'max:255'],
            'newQuantity' => ['nullable', 'integer', 'min:1'],
            'newInstructions' => ['nullable', 'string', 'max:1000'],
        ], [
            'newCustomName.required_without' => 'Pick a medication from the formulary or type a name.',
        ]);

        $name = $validated['newMedicationId']
            ? Medication::find($validated['newMedicationId'])->name
            : $validated['newCustomName'];

        $this->prescriptionItems[] = [
            'medication_id' => $validated['newMedicationId'] ?: null,
            'name' => $name,
            'dosage' => $validated['newDosage'] ?: null,
            'frequency' => $validated['newFrequency'] ?: null,
            'duration' => $validated['newDuration'] ?: null,
            'quantity' => $validated['newQuantity'] ?: null,
            'instructions' => $validated['newInstructions'] ?: null,
        ];

        $this->reset(['newMedicationId', 'newCustomName', 'newDosage', 'newFrequency', 'newDuration', 'newQuantity', 'newInstructions']);
        $this->resetErrorBag();
    }

    public function removePrescriptionItem(int $index): void
    {
        Gate::authorize('update', $this->encounter);

        unset($this->prescriptionItems[$index]);
        $this->prescriptionItems = array_values($this->prescriptionItems);
    }

    protected function syncInvestigations(): void
    {
        $prices = Investigation::whereIn('id', $this->selectedInvestigations)
            ->pluck('price', 'id');

        $this->encounter->investigations()->sync(
            $prices->mapWithKeys(fn ($price, $id) => [$id => ['price' => $price]])
        );
    }

    protected function syncPrescription(): void
    {
        if (empty($this->prescriptionItems)) {
            $this->encounter->prescription?->items()->delete();

            return;
        }

        $prescription = Prescription::firstOrCreate(
            ['encounter_id' => $this->encounter->id],
            ['patient_id' => $this->patient->id, 'practitioner_id' => $this->encounter->practitioner_id, 'status' => 'pending']
        );

        $prescription->items()->delete();

        foreach ($this->prescriptionItems as $item) {
            $prescription->items()->create([
                'medication_id' => $item['medication_id'],
                'custom_name' => $item['medication_id'] ? null : $item['name'],
                'dosage' => $item['dosage'],
                'frequency' => $item['frequency'],
                'duration' => $item['duration'],
                'quantity' => $item['quantity'],
                'instructions' => $item['instructions'],
            ]);
        }
    }

    public function saveDraft(): void
    {
        Gate::authorize('update', $this->encounter);

        $validated = $this->validate();

        $this->encounter->update([
            'patient_note' => $validated['patient_note'],
            'results' => $validated['results'],
            'follow_up_date' => $validated['follow_up_date'] ?: null,
            'follow_up_reason' => $validated['follow_up_reason'],
            'status' => 'draft',
        ]);

        $this->syncInvestigations();
        $this->syncPrescription();

        session()->flash('status', 'Draft saved.');
    }

    public function save(): void
    {
        Gate::authorize('update', $this->encounter);

        $validated = $this->validate();

        $this->encounter->update([
            'patient_note' => $validated['patient_note'],
            'results' => $validated['results'],
            'follow_up_date' => $validated['follow_up_date'] ?: null,
            'follow_up_reason' => $validated['follow_up_reason'],
            'status' => 'completed',
            'finalized_at' => now(),
        ]);

        $this->syncInvestigations();
        $this->syncPrescription();

        session()->flash('status', 'Encounter saved and closed.');

        // A plain redirect (not navigate:true) here on purpose: this page has
        // a debounced live autosave on patient_note, and an SPA-style
        // wire:navigate transition can leave that debounce timer to fire
        // after the component's already been torn down. A full page load
        // cleanly discards any pending timer instead.
        $this->redirect(route('patients.show', $this->patient));
    }

    public function startEditingMedical(): void
    {
        Gate::authorize('updateMedical', $this->patient);

        $this->editingMedical = true;
    }

    public function saveMedical(): void
    {
        Gate::authorize('updateMedical', $this->patient);

        $validated = $this->validate([
            'main_complaint' => ['nullable', 'string', 'max:2000'],
            'medical_history' => ['nullable', 'string', 'max:5000'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'current_medications' => ['nullable', 'string', 'max:2000'],
            'previous_treatments' => ['nullable', 'string', 'max:5000'],
        ]);

        PatientMedicalInfo::updateOrCreate(
            ['patient_id' => $this->patient->id],
            [...$validated, 'updated_by' => auth()->id()]
        );

        $this->patient->unsetRelation('medicalInfo');
        $this->editingMedical = false;

        session()->flash('status', 'Medical record updated.');
    }

    public function render()
    {
        return view('livewire.encounters.encounter-show', [
            'canEdit' => Gate::allows('update', $this->encounter),
            'canUpdateMedical' => Gate::allows('updateMedical', $this->patient),
            'investigationCatalog' => Investigation::where('is_active', true)
                ->orderBy('category')->orderBy('subcategory')->orderBy('name')
                ->get()
                ->groupBy('category'),
            'orderedInvestigations' => $this->encounter->investigations()
                ->orderBy('category')->orderBy('subcategory')->orderBy('name')
                ->get()
                ->groupBy('category'),
            'medicationCatalog' => Medication::where('is_active', true)->orderBy('name')->get(),
        ])->extends('layouts.app')->title("Encounter - {$this->patient->full_name}");
    }
}
