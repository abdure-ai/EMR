<?php

namespace Tests\Feature\Billing;

use App\Livewire\Appointments\AppointmentIndex;
use App\Livewire\Billing\InvoiceIndex;
use App\Livewire\Billing\InvoiceShow;
use App\Livewire\Patients\PatientCreate;
use App\Models\Appointment;
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

class CheckInBillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        ClinicSetting::current()->update([
            'new_patient_card_fee' => 100,
            'revisit_free_within_days' => 30,
        ]);
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

    protected function makeService(): Service
    {
        $department = Department::create(['name' => 'General Medicine']);

        return Service::create(['department_id' => $department->id, 'name' => 'Initial Consultation', 'duration_minutes' => 45, 'price' => 300]);
    }

    protected function makeReception(): User
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        return $reception;
    }

    public function test_registering_a_patient_sends_one_combined_invoice_to_the_cashier(): void
    {
        // The card fee and the first visit's service fee are billed
        // together as a single invoice - the cashier shouldn't have to
        // process two separate payments for one walk-in registration.
        $reception = $this->makeReception();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        $this->actingAs($reception);

        Livewire::test(PatientCreate::class)
            ->set('first_name', 'Kebede')
            ->set('last_name', 'Desalgn')
            ->set('sex', 'male')
            ->set('age', '35')
            ->set('phone', '+251911000099')
            ->set('department_id', (string) $service->department_id)
            ->set('service_id', (string) $service->id)
            ->set('practitioner_id', (string) $practitioner->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, Invoice::count());

        $invoice = Invoice::first();
        $this->assertSame('visit', $invoice->type);
        $this->assertSame('pending', $invoice->status);
        $this->assertSame($practitioner->id, $invoice->practitioner_id);
        $this->assertSame($service->id, $invoice->service_id);
        $this->assertEquals(400.00, $invoice->total_amount); // 100 card fee + 300 service
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{6}$/', $invoice->invoice_number);
        $this->assertSame(2, $invoice->lineItems()->count());
        $this->assertDatabaseHas('invoice_line_items', ['invoice_id' => $invoice->id, 'description' => 'New Patient Registration Card', 'amount' => 100]);
        $this->assertDatabaseHas('invoice_line_items', ['invoice_id' => $invoice->id, 'description' => $service->name, 'amount' => 300]);

        // And it's visible to the cashier under Billing.
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $this->actingAs($cashier);

        Livewire::test(InvoiceIndex::class)
            ->assertSee($invoice->invoice_number)
            ->assertSee('Kebede');
    }

    public function test_registration_charges_only_the_visit_fee_when_the_card_fee_is_zero(): void
    {
        ClinicSetting::current()->update(['new_patient_card_fee' => 0]);

        $reception = $this->makeReception();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        $this->actingAs($reception);

        Livewire::test(PatientCreate::class)
            ->set('first_name', 'Kebede')
            ->set('last_name', 'Desalgn')
            ->set('sex', 'male')
            ->set('age', '35')
            ->set('phone', '+251911000098')
            ->set('department_id', (string) $service->department_id)
            ->set('service_id', (string) $service->id)
            ->set('practitioner_id', (string) $practitioner->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, Invoice::count());

        $invoice = Invoice::first();
        $this->assertSame('visit', $invoice->type);
        $this->assertEquals(300.00, $invoice->total_amount);
        $this->assertSame(1, $invoice->lineItems()->count());
    }

    public function test_registering_a_patient_queues_directly_when_nothing_is_owed(): void
    {
        ClinicSetting::current()->update(['new_patient_card_fee' => 0]);

        $reception = $this->makeReception();
        $department = Department::create(['name' => 'General Medicine']);
        $freeService = Service::create(['department_id' => $department->id, 'name' => 'Free Checkup', 'duration_minutes' => 20, 'price' => 0]);
        $practitioner = $this->makePractitioner();

        $this->actingAs($reception);

        Livewire::test(PatientCreate::class)
            ->set('first_name', 'Kebede')
            ->set('last_name', 'Desalgn')
            ->set('sex', 'male')
            ->set('age', '35')
            ->set('phone', '+251911000097')
            ->set('department_id', (string) $freeService->department_id)
            ->set('service_id', (string) $freeService->id)
            ->set('practitioner_id', (string) $practitioner->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(0, Invoice::count());
        $this->assertSame(1, QueueEntry::count());
    }

    public function test_appointment_check_in_charges_the_service_price_not_the_card_fee(): void
    {
        // The card fee already went out at registration - checking in for the
        // first-ever visit should only charge the service, not double the fee.
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        $appointment = Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
        ]);

        $this->actingAs($reception);

        Livewire::test(AppointmentIndex::class)->call('checkIn', $appointment->id);

        $this->assertSame('awaiting_payment', $appointment->fresh()->status);
        $this->assertSame(0, QueueEntry::count());

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertSame('visit', $invoice->type);
        $this->assertEquals(300.00, $invoice->total_amount);
        $this->assertSame(1, $invoice->lineItems()->count());
    }

    public function test_revisit_beyond_free_window_is_charged_the_service_price(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        // Patient completed a visit 40 days ago - beyond the 30-day free window.
        QueueEntry::create([
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
            'status' => 'completed',
            'check_in_time' => now()->subDays(40),
            'completed_at' => now()->subDays(40),
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
        ]);

        $this->actingAs($reception);

        Livewire::test(AppointmentIndex::class)->call('checkIn', $appointment->id);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals(300.00, $invoice->total_amount);
        $this->assertSame(1, $invoice->lineItems()->count());
    }

    public function test_revisit_within_free_window_skips_billing_entirely(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        // Completed a visit 5 days ago - within the 30-day free window.
        QueueEntry::create([
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
            'status' => 'completed',
            'check_in_time' => now()->subDays(5),
            'completed_at' => now()->subDays(5),
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
        ]);

        $this->actingAs($reception);

        Livewire::test(AppointmentIndex::class)->call('checkIn', $appointment->id);

        $this->assertSame(0, Invoice::count());
        $this->assertSame('checked_in', $appointment->fresh()->status);
        $this->assertDatabaseHas('queue_entries', [
            'appointment_id' => $appointment->id,
            'status' => 'waiting',
        ]);
    }

    public function test_cashier_processing_a_visit_invoice_creates_the_queue_entry(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');
        $patient = $this->makePatient();
        $service = $this->makeService();
        $practitioner = $this->makePractitioner();

        $appointment = Appointment::create([
            'patient_id' => $patient->id, 'service_id' => $service->id,
            'practitioner_id' => $practitioner->id, 'scheduled_at' => today()->setTime(9, 0),
            'status' => 'awaiting_payment',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id, 'appointment_id' => $appointment->id,
            'type' => 'visit', 'practitioner_id' => $practitioner->id, 'service_id' => $service->id,
            'status' => 'pending', 'total_amount' => 300,
        ]);
        $invoice->lineItems()->create(['description' => $service->name, 'amount' => 300]);

        $this->actingAs($cashier);

        Livewire::test(InvoiceShow::class, ['invoice' => $invoice])
            ->set('paymentMethod', 'cash')
            ->call('confirmPayment')
            ->assertHasNoErrors();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('checked_in', $appointment->fresh()->status);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 300]);
        $this->assertDatabaseHas('queue_entries', [
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
        ]);
    }

    public function test_cashier_processing_a_registration_invoice_does_not_create_a_queue_entry(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');
        $patient = $this->makePatient();

        $invoice = Invoice::create([
            'patient_id' => $patient->id, 'type' => 'registration',
            'status' => 'pending', 'total_amount' => 100,
        ]);
        $invoice->lineItems()->create(['description' => 'New Patient Registration Card', 'amount' => 100]);

        $this->actingAs($cashier);

        Livewire::test(InvoiceShow::class, ['invoice' => $invoice])
            ->set('paymentMethod', 'cash')
            ->call('confirmPayment')
            ->assertHasNoErrors();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame(0, QueueEntry::count());
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 100]);
    }

    public function test_reception_cannot_process_payments(): void
    {
        $reception = $this->makeReception();
        $patient = $this->makePatient();

        $invoice = Invoice::create([
            'patient_id' => $patient->id, 'type' => 'registration',
            'status' => 'pending', 'total_amount' => 100,
        ]);

        $this->actingAs($reception)
            ->get(route('billing.index'))
            ->assertForbidden();

        $this->actingAs($reception)
            ->get(route('billing.show', $invoice))
            ->assertForbidden();
    }
}
