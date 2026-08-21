<?php

namespace Tests\Feature\Patients;

use App\Livewire\Patients\PatientCreate;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeDepartmentAndService(): Service
    {
        $department = Department::create(['name' => 'General Medicine']);

        return Service::create(['department_id' => $department->id, 'name' => 'Initial Consultation', 'duration_minutes' => 45, 'price' => 300]);
    }

    protected function makePractitioner(): User
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');

        return $practitioner;
    }

    public function test_reception_can_register_a_patient_and_assign_them_for_todays_visit(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');
        $service = $this->makeDepartmentAndService();
        $practitioner = $this->makePractitioner();

        $this->actingAs($reception);

        Livewire::test(PatientCreate::class)
            ->set('first_name', 'Meron')
            ->set('middle_name', 'Getu')
            ->set('last_name', 'Tesfaye')
            ->set('sex', 'female')
            ->set('age', '32')
            ->set('phone', '+251911000000')
            ->set('region', 'Oromia')
            ->set('zone', 'West Shewa')
            ->set('woreda', 'Ambo')
            ->set('kebele', '02')
            ->set('house_no', '14')
            ->set('department_id', (string) $service->department_id)
            ->set('service_id', (string) $service->id)
            ->set('practitioner_id', (string) $practitioner->id)
            ->call('save')
            ->assertHasNoErrors();

        $patient = Patient::first();

        $this->assertNotNull($patient);
        $this->assertSame('Meron', $patient->first_name);
        $this->assertSame('Oromia', $patient->region);
        $this->assertMatchesRegularExpression('/^NES-\d{4}-\d{6}$/', $patient->patient_id);
        $this->assertSame($reception->id, $patient->created_by);

        // A visit invoice for the assigned doctor's service should already be
        // sitting with the cashier, alongside the registration fee.
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id, 'type' => 'visit',
            'practitioner_id' => $practitioner->id, 'service_id' => $service->id,
        ]);
    }

    public function test_registration_fails_validation_when_required_fields_are_missing(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception);

        Livewire::test(PatientCreate::class)
            ->set('first_name', '')
            ->set('last_name', '')
            ->set('sex', '')
            ->set('age', '')
            ->set('phone', '')
            ->set('department_id', '')
            ->set('service_id', '')
            ->set('practitioner_id', '')
            ->call('save')
            ->assertHasErrors(['first_name', 'last_name', 'sex', 'age', 'phone', 'department_id', 'service_id', 'practitioner_id']);

        $this->assertSame(0, Patient::count());
    }

    public function test_cashier_cannot_access_patient_registration(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $this->actingAs($cashier)
            ->get(route('patients.create'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_visiting_patient_list(): void
    {
        $this->get(route('patients.index'))
            ->assertRedirect(route('login'));
    }
}
