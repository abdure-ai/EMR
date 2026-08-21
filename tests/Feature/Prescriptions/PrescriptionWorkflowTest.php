<?php

namespace Tests\Feature\Prescriptions;

use App\Livewire\Prescriptions\PrescriptionIndex;
use App\Livewire\Prescriptions\PrescriptionShow;
use App\Models\InventoryBatch;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\QueueEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrescriptionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePatient(string $phone = '+251900000001'): Patient
    {
        return Patient::create([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => $phone,
        ]);
    }

    protected function makePractitioner(): User
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');

        return $practitioner;
    }

    protected function makePharmacist(): User
    {
        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('Pharmacist');

        return $pharmacist;
    }

    protected function makePrescriptionWithItem(): Prescription
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;

        $prescription = Prescription::create([
            'encounter_id' => $encounter->id, 'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id, 'status' => 'pending',
        ]);

        $prescription->items()->create(['custom_name' => 'Paracetamol', 'dosage' => '500mg', 'quantity' => 10]);

        return $prescription;
    }

    public function test_pharmacist_sees_pending_prescriptions_with_items(): void
    {
        $pharmacist = $this->makePharmacist();
        $prescription = $this->makePrescriptionWithItem();

        $this->actingAs($pharmacist);

        Livewire::test(PrescriptionIndex::class)
            ->assertSee($prescription->patient->full_name);
    }

    public function test_empty_prescriptions_are_not_listed(): void
    {
        $pharmacist = $this->makePharmacist();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;
        Prescription::create([
            'encounter_id' => $encounter->id, 'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id, 'status' => 'pending',
        ]);

        $this->actingAs($pharmacist);

        Livewire::test(PrescriptionIndex::class)
            ->assertDontSee($patient->full_name);
    }

    public function test_pharmacist_can_dispense_a_pending_prescription(): void
    {
        $pharmacist = $this->makePharmacist();
        $prescription = $this->makePrescriptionWithItem();

        $this->actingAs($pharmacist);

        Livewire::test(PrescriptionShow::class, ['prescription' => $prescription])
            ->call('dispense')
            ->assertHasNoErrors();

        $prescription->refresh();
        $this->assertSame('dispensed', $prescription->status);
        $this->assertNotNull($prescription->dispensed_at);
        $this->assertSame($pharmacist->id, $prescription->dispensed_by);
    }

    public function test_cannot_dispense_an_already_dispensed_prescription(): void
    {
        $pharmacist = $this->makePharmacist();
        $prescription = $this->makePrescriptionWithItem();
        $prescription->update(['status' => 'dispensed', 'dispensed_at' => now()]);

        $this->actingAs($pharmacist);

        Livewire::test(PrescriptionShow::class, ['prescription' => $prescription])
            ->call('dispense')
            ->assertForbidden();
    }

    public function test_dispensing_deducts_formulary_stock(): void
    {
        $pharmacist = $this->makePharmacist();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $medication = Medication::create(['name' => 'Black Seed Oil']);
        InventoryBatch::create([
            'medication_id' => $medication->id, 'quantity_received' => 10, 'quantity_remaining' => 10,
            'received_at' => now()->toDateString(),
        ]);
        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;
        $prescription = Prescription::create([
            'encounter_id' => $encounter->id, 'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id, 'status' => 'pending',
        ]);
        $prescription->items()->create(['medication_id' => $medication->id, 'quantity' => 3]);

        $this->actingAs($pharmacist);

        Livewire::test(PrescriptionShow::class, ['prescription' => $prescription])
            ->call('dispense')
            ->assertHasNoErrors();

        $this->assertSame(7, $medication->currentStock());
    }

    public function test_reception_cannot_view_prescriptions(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $prescription = $this->makePrescriptionWithItem();

        $this->actingAs($reception)
            ->get(route('prescriptions.index'))
            ->assertForbidden();

        $this->actingAs($reception)
            ->get(route('prescriptions.show', $prescription))
            ->assertForbidden();
    }

    public function test_clinic_manager_can_also_dispense(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $prescription = $this->makePrescriptionWithItem();

        $this->actingAs($manager);

        Livewire::test(PrescriptionShow::class, ['prescription' => $prescription])
            ->call('dispense')
            ->assertHasNoErrors();

        $this->assertSame('dispensed', $prescription->fresh()->status);
    }

    public function test_clinic_manager_can_create_a_medication(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        $this->actingAs($manager);

        Livewire::test(\App\Livewire\Medications\MedicationCreate::class)
            ->set('name', 'Honey Elixir')
            ->set('form', 'Syrup')
            ->set('strength', '250ml')
            ->set('price', '180')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('medications', ['name' => 'Honey Elixir']);
    }

    public function test_reception_cannot_manage_medications(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('medications.index'))
            ->assertForbidden();
    }
}
