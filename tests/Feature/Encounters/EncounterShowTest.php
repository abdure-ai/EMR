<?php

namespace Tests\Feature\Encounters;

use App\Livewire\Encounters\EncounterShow;
use App\Models\Investigation;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EncounterShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePatient(): Patient
    {
        return Patient::create([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => '+251900000001',
        ]);
    }

    protected function makePractitioner(string $name = 'Dr Bekele'): User
    {
        $practitioner = User::factory()->create(['name' => $name]);
        $practitioner->assignRole('Practitioner');

        return $practitioner;
    }

    public function test_queue_check_in_auto_creates_a_draft_encounter(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();

        $queueEntry = QueueEntry::create([
            'patient_id' => $patient->id, 'practitioner_id' => $practitioner->id,
        ]);

        $this->assertNotNull($queueEntry->encounter);
        $this->assertSame('draft', $queueEntry->encounter->status);
        $this->assertSame($practitioner->id, $queueEntry->encounter->practitioner_id);
    }

    public function test_practitioner_can_save_a_draft_of_their_own_encounter(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;
        $investigation = Investigation::create(['category' => 'lab', 'subcategory' => 'Hematology', 'name' => 'CBC', 'price' => 150]);

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('patient_note', 'Complains of headache.')
            ->set('selectedInvestigations', [(string) $investigation->id])
            ->call('saveDraft')
            ->assertHasNoErrors();

        $encounter->refresh();
        $this->assertSame('draft', $encounter->status);
        $this->assertSame('Complains of headache.', $encounter->patient_note);
        $this->assertNull($encounter->finalized_at);
        $this->assertTrue($encounter->investigations->contains($investigation));
        $this->assertEquals(150.00, $encounter->investigations->first()->pivot->price);
    }

    public function test_patient_note_autosaves_as_a_draft_while_typing(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('patient_note', 'Typing a note...')
            ->assertHasNoErrors();

        $this->assertSame('Typing a note...', $encounter->fresh()->patient_note);
        $this->assertSame('draft', $encounter->fresh()->status);
    }

    public function test_practitioner_can_finalize_their_own_encounter(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('patient_note', 'Resolved.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('patients.show', $patient));

        $encounter->refresh();
        $this->assertSame('completed', $encounter->status);
        $this->assertNotNull($encounter->finalized_at);
    }

    public function test_practitioner_can_add_a_prescription_item_from_the_formulary(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;
        $medication = Medication::create(['name' => 'Black Seed Oil', 'strength' => '100ml']);

        $this->actingAs($practitioner);

        $component = Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('newMedicationId', (string) $medication->id)
            ->set('newDosage', '10ml')
            ->set('newFrequency', 'Once daily')
            ->set('newDuration', '7 days')
            ->call('addPrescriptionItem')
            ->assertHasNoErrors();

        $this->assertCount(1, $component->get('prescriptionItems'));

        $component->call('save')->assertHasNoErrors();

        $prescription = $encounter->prescription()->first();
        $this->assertNotNull($prescription);
        $this->assertSame('pending', $prescription->status);
        $this->assertSame(1, $prescription->items()->count());
        $item = $prescription->items()->first();
        $this->assertSame($medication->id, $item->medication_id);
        $this->assertSame('10ml', $item->dosage);
    }

    public function test_practitioner_can_add_a_custom_named_prescription_item(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('newCustomName', 'Aspirin')
            ->call('addPrescriptionItem')
            ->assertHasNoErrors()
            ->call('saveDraft')
            ->assertHasNoErrors();

        $item = $encounter->prescription()->first()->items()->first();
        $this->assertSame('Aspirin', $item->custom_name);
        $this->assertNull($item->medication_id);
    }

    public function test_adding_a_prescription_item_without_a_name_fails_validation(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->call('addPrescriptionItem')
            ->assertHasErrors(['newCustomName']);
    }

    public function test_practitioner_cannot_edit_a_colleagues_draft_encounter(): void
    {
        $patient = $this->makePatient();
        $owner = $this->makePractitioner('Dr Owner');
        $other = $this->makePractitioner('Dr Other');
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $owner->id]);
        $encounter = $queueEntry->encounter;

        $this->actingAs($other);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->call('saveDraft')
            ->assertForbidden();
    }

    public function test_completed_encounter_is_read_only_even_for_its_own_practitioner(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;
        $encounter->update(['status' => 'completed', 'finalized_at' => now(), 'patient_note' => 'Locked note.']);

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->assertSee('Locked note.')
            ->call('save')
            ->assertForbidden();
    }

    public function test_reception_cannot_view_an_encounter(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);

        $this->actingAs($reception)
            ->get(route('encounters.show', [$patient, $queueEntry->encounter]))
            ->assertForbidden();
    }

    public function test_clinic_manager_can_edit_any_practitioners_draft_encounter(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $queueEntry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $encounter = $queueEntry->encounter;

        $this->actingAs($manager);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('results', 'Normal.')
            ->call('saveDraft')
            ->assertHasNoErrors();

        $this->assertSame('Normal.', $encounter->fresh()->results);
    }
}
