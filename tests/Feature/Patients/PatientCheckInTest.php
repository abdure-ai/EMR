<?php

namespace Tests\Feature\Patients;

use App\Livewire\Patients\PatientCheckIn;
use App\Models\ClinicSetting;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientCheckInTest extends TestCase
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

    protected function makeService(?float $price = null): Service
    {
        $department = Department::create(['name' => 'General Medicine']);

        return Service::create(['department_id' => $department->id, 'name' => 'Free Follow-up', 'duration_minutes' => 15, 'price' => $price]);
    }

    protected function makeReception(): User
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        return $reception;
    }

    /**
     * Billing rules (new-patient fee, revisit fee) are covered in
     * tests/Feature/Billing/CheckInBillingTest.php - this just proves the
     * revisit check-in mechanism itself queues someone when nothing is owed.
     */
    public function test_reception_can_check_in_a_returning_patient_with_no_charge(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $service = $this->makeService(null);

        $this->actingAs($reception);

        Livewire::test(PatientCheckIn::class, ['patient' => $patient])
            ->set('department_id', (string) $service->department_id)
            ->set('service_id', (string) $service->id)
            ->set('practitioner_id', (string) $practitioner->id)
            ->call('checkIn')
            ->assertHasNoErrors();

        $entry = QueueEntry::first();
        $this->assertNotNull($entry);
        $this->assertNull($entry->appointment_id);
        $this->assertSame('waiting', $entry->status);
        $this->assertMatchesRegularExpression('/^\d{3}$/', $entry->token_number);
    }

    public function test_check_in_sends_a_chargeable_revisit_to_the_cashier_instead(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        $service = $this->makeService(300);

        $this->actingAs($reception);

        Livewire::test(PatientCheckIn::class, ['patient' => $patient])
            ->set('department_id', (string) $service->department_id)
            ->set('service_id', (string) $service->id)
            ->set('practitioner_id', (string) $practitioner->id)
            ->call('checkIn')
            ->assertHasNoErrors();

        $this->assertSame(0, QueueEntry::count());
        $this->assertDatabaseHas('invoices', ['patient_id' => $patient->id, 'status' => 'pending']);
    }

    public function test_cannot_check_in_a_patient_who_already_has_an_active_visit(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $practitioner = $this->makePractitioner();
        QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);

        $this->actingAs($reception);

        Livewire::test(PatientCheckIn::class, ['patient' => $patient])
            ->assertSet('alreadyActive', true)
            ->assertSee('Already checked in');
    }

    public function test_cannot_check_in_a_patient_with_a_pending_visit_invoice(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        Invoice::create(['patient_id' => $patient->id, 'type' => 'visit', 'status' => 'pending', 'total_amount' => 300]);

        $this->actingAs($reception);

        Livewire::test(PatientCheckIn::class, ['patient' => $patient])
            ->assertSet('alreadyActive', true);
    }

    public function test_token_numbers_do_not_repeat_within_the_same_day(): void
    {
        $practitioner = $this->makePractitioner();
        $patient1 = $this->makePatient('+251900000001');
        $patient2 = $this->makePatient('+251900000002');

        $entry1 = QueueEntry::create(['patient_id' => $patient1->id, 'practitioner_id' => $practitioner->id]);
        $entry2 = QueueEntry::create(['patient_id' => $patient2->id, 'practitioner_id' => $practitioner->id]);

        $this->assertNotSame($entry1->token_number, $entry2->token_number);
    }

    public function test_pharmacist_cannot_check_in_a_patient(): void
    {
        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('Pharmacist');
        $patient = $this->makePatient();

        $this->actingAs($pharmacist)
            ->get(route('patients.check-in', $patient))
            ->assertForbidden();
    }
}
