<?php

namespace Tests\Feature\Appointments;

use App\Livewire\Appointments\AppointmentCreate;
use App\Livewire\Appointments\AppointmentIndex;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentTest extends TestCase
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
            'first_name' => 'Sara',
            'last_name' => 'Ibrahim',
            'sex' => 'female',
            'age' => 30,
            'phone' => '+251900000001',
        ]);
    }

    protected function makeService(): Service
    {
        $department = Department::create(['name' => 'General Medicine']);

        return Service::create(['department_id' => $department->id, 'name' => 'Initial Consultation', 'duration_minutes' => 45]);
    }

    protected function makePractitioner(): User
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');

        return $practitioner;
    }

    public function test_reception_can_book_an_appointment(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $patient = $this->makePatient();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        $this->actingAs($reception);

        Livewire::test(AppointmentCreate::class)
            ->call('selectPatient', $patient->id)
            ->set('service_id', $service->id)
            ->set('practitioner_id', $practitioner->id)
            ->set('scheduled_date', today()->toDateString())
            ->set('scheduled_time', '10:30')
            ->set('source', 'phone')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, Appointment::count());
        $this->assertSame('booked', Appointment::first()->status);
    }

    public function test_booking_fails_validation_without_a_patient(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        $this->actingAs($reception);

        Livewire::test(AppointmentCreate::class)
            ->set('service_id', $service->id)
            ->set('practitioner_id', $practitioner->id)
            ->set('scheduled_date', today()->toDateString())
            ->set('scheduled_time', '10:30')
            ->call('save')
            ->assertHasErrors(['patient_id']);

        $this->assertSame(0, Appointment::count());
    }

    public function test_pharmacist_cannot_book_appointments(): void
    {
        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('Pharmacist');

        $this->actingAs($pharmacist)
            ->get(route('appointments.create'))
            ->assertForbidden();
    }

    public function test_practitioner_only_sees_their_own_appointments(): void
    {
        $practitionerA = $this->makePractitioner();
        $practitionerB = $this->makePractitioner();
        $service = $this->makeService();

        $patientA = $this->makePatient();
        $patientB = Patient::create([
            'first_name' => 'Other', 'last_name' => 'Patient', 'sex' => 'male',
            'age' => 40, 'phone' => '+251900000002',
        ]);

        // Checked in, so identity-masking doesn't interfere with what this test
        // is actually checking: practitioner-to-practitioner isolation.
        Appointment::create([
            'patient_id' => $patientA->id, 'service_id' => $service->id,
            'practitioner_id' => $practitionerA->id, 'scheduled_at' => today()->setTime(9, 0),
            'status' => 'checked_in',
        ]);
        Appointment::create([
            'patient_id' => $patientB->id, 'service_id' => $service->id,
            'practitioner_id' => $practitionerB->id, 'scheduled_at' => today()->setTime(10, 0),
            'status' => 'checked_in',
        ]);

        $this->actingAs($practitionerA);

        Livewire::test(AppointmentIndex::class)
            ->assertSee('Sara')
            ->assertDontSee('Other');
    }

    public function test_practitioner_cannot_see_patient_identity_while_awaiting_payment(): void
    {
        $practitioner = $this->makePractitioner();
        $service = $this->makeService();
        $patient = $this->makePatient();

        $appointment = Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
            'status' => 'awaiting_payment',
        ]);

        $this->actingAs($practitioner);

        Livewire::test(AppointmentIndex::class)
            ->assertDontSee('Sara')
            ->assertSee('Hidden until check-in');

        $appointment->update(['status' => 'checked_in']);

        Livewire::test(AppointmentIndex::class)
            ->assertSee('Sara')
            ->assertDontSee('Hidden until check-in');
    }

    public function test_practitioner_cannot_see_patient_identity_before_check_in(): void
    {
        // Even a freshly booked appointment - before reception has checked the
        // patient in and long before any payment is involved - must not leak
        // the patient's identity to the practitioner.
        $practitioner = $this->makePractitioner();
        $service = $this->makeService();
        $patient = $this->makePatient();

        Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
            'status' => 'booked',
        ]);

        $this->actingAs($practitioner);

        Livewire::test(AppointmentIndex::class)
            ->assertDontSee('Sara')
            ->assertSee('Hidden until check-in');
    }

    public function test_reception_can_see_patient_identity_while_awaiting_payment(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $practitioner = $this->makePractitioner();
        $service = $this->makeService();
        $patient = $this->makePatient();

        Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
            'status' => 'awaiting_payment',
        ]);

        $this->actingAs($reception);

        Livewire::test(AppointmentIndex::class)
            ->assertSee('Sara');
    }

    public function test_check_in_creates_a_queue_entry_and_updates_status(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $patient = $this->makePatient();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        $appointment = Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
        ]);

        $this->actingAs($reception);

        Livewire::test(AppointmentIndex::class)
            ->call('checkIn', $appointment->id);

        $this->assertSame('checked_in', $appointment->fresh()->status);
        $this->assertDatabaseHas('queue_entries', [
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'status' => 'waiting',
        ]);
    }
}
