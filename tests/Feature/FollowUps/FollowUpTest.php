<?php

namespace Tests\Feature\FollowUps;

use App\Livewire\Encounters\EncounterShow;
use App\Livewire\FollowUps\FollowUpIndex;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FollowUpTest extends TestCase
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

    protected function makeReception(): User
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        return $reception;
    }

    protected function makeCompletedEncounter(Patient $patient, User $practitioner, array $overrides = []): Encounter
    {
        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;
        $encounter->update(array_merge(['status' => 'completed', 'finalized_at' => now()], $overrides));

        return $encounter->fresh();
    }

    public function test_practitioner_can_set_a_follow_up_date_when_finalizing_an_encounter(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('follow_up_date', now()->addDays(14)->toDateString())
            ->set('follow_up_reason', 'Recheck progress')
            ->call('save')
            ->assertHasNoErrors();

        $encounter->refresh();
        $this->assertEquals(now()->addDays(14)->toDateString(), $encounter->follow_up_date->toDateString());
        $this->assertSame('Recheck progress', $encounter->follow_up_reason);
    }

    public function test_follow_up_date_cannot_be_in_the_past(): void
    {
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;

        $this->actingAs($practitioner);

        Livewire::test(EncounterShow::class, ['patient' => $patient, 'encounter' => $encounter])
            ->set('follow_up_date', now()->subDays(2)->toDateString())
            ->call('saveDraft')
            ->assertHasErrors(['follow_up_date']);
    }

    public function test_overdue_follow_up_appears_on_the_list(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $this->makeCompletedEncounter($patient, $practitioner, [
            'follow_up_date' => now()->subDays(3), 'follow_up_reason' => 'Recheck',
        ]);

        $this->actingAs($reception);

        Livewire::test(FollowUpIndex::class)
            ->assertSee($patient->full_name)
            ->assertSee('3 days overdue');
    }

    public function test_future_follow_up_does_not_appear_yet(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $this->makeCompletedEncounter($patient, $practitioner, ['follow_up_date' => now()->addDays(5)]);

        $this->actingAs($reception);

        Livewire::test(FollowUpIndex::class)
            ->assertDontSee($patient->full_name);
    }

    public function test_follow_up_is_cleared_once_the_patient_returns(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $encounter = $this->makeCompletedEncounter($patient, $practitioner, ['follow_up_date' => now()->subDays(1)]);

        // Patient checks in again after the encounter was recorded - forced a
        // minute ahead so it doesn't land in the same DB-stored second as the
        // encounter's own created_at.
        QueueEntry::create([
            'patient_id' => $patient->id, 'practitioner_id' => $practitioner->id,
            'check_in_time' => now()->addMinute(),
        ]);

        $this->assertTrue($encounter->hasPatientReturnedSinceFollowUp());

        $this->actingAs($reception);

        Livewire::test(FollowUpIndex::class)
            ->assertDontSee($patient->full_name);
    }

    public function test_reception_can_dismiss_a_follow_up(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $encounter = $this->makeCompletedEncounter($patient, $practitioner, ['follow_up_date' => now()->subDays(1)]);

        $this->actingAs($reception);

        Livewire::test(FollowUpIndex::class)
            ->call('dismiss', $encounter->id)
            ->assertHasNoErrors();

        $this->assertNotNull($encounter->fresh()->follow_up_dismissed_at);

        Livewire::test(FollowUpIndex::class)
            ->assertDontSee($patient->full_name);
    }

    public function test_practitioner_cannot_access_the_follow_up_list(): void
    {
        $practitioner = $this->makePractitioner();

        $this->actingAs($practitioner)
            ->get(route('follow-ups.index'))
            ->assertForbidden();
    }

    public function test_pharmacist_cannot_access_the_follow_up_list(): void
    {
        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('Pharmacist');

        $this->actingAs($pharmacist)
            ->get(route('follow-ups.index'))
            ->assertForbidden();
    }
}
