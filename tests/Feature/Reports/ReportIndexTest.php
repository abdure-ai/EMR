<?php

namespace Tests\Feature\Reports;

use App\Livewire\Reports\ReportIndex;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeManager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        return $manager;
    }

    protected function makePatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => '+251900000001'.random_int(0, 999),
        ], $overrides));
    }

    protected function makeService(): Service
    {
        $department = Department::create(['name' => 'General Medicine']);

        return Service::create(['department_id' => $department->id, 'name' => 'Consultation', 'duration_minutes' => 30, 'price' => 300]);
    }

    protected function makePaidVisit(User $practitioner, Service $service, float $amount, string $method = 'cash'): Invoice
    {
        $patient = $this->makePatient();

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'type' => 'visit',
            'practitioner_id' => $practitioner->id,
            'service_id' => $service->id,
            'status' => 'paid',
            'total_amount' => $amount,
            'paid_at' => now(),
        ]);

        Payment::create(['invoice_id' => $invoice->id, 'amount' => $amount, 'method' => $method]);

        return $invoice;
    }

    public function test_non_manager_cannot_view_reports(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');

        $this->actingAs($practitioner)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_manager_can_view_reports(): void
    {
        $this->actingAs($this->makeManager())
            ->get(route('reports.index'))
            ->assertOk();
    }

    public function test_summary_totals_reflect_data_within_the_selected_range(): void
    {
        $manager = $this->makeManager();
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $service = $this->makeService();

        $inRange = $this->makePaidVisit($practitioner, $service, 300);
        $inRange->forceFill(['created_at' => now()])->save();
        $inRange->payments()->first()->forceFill(['created_at' => now()])->save();

        $outOfRange = $this->makePaidVisit($practitioner, $service, 500);
        $outOfRange->forceFill(['created_at' => now()->subMonths(3)])->save();
        $outOfRange->payments()->first()->forceFill(['created_at' => now()->subMonths(3)])->save();

        $this->actingAs($manager);

        Livewire::test(ReportIndex::class)
            ->call('setPreset', 'today')
            ->assertSee('300.00 ETB')
            ->assertDontSee('500.00 ETB');
    }

    public function test_revenue_breaks_down_by_service_and_practitioner(): void
    {
        $manager = $this->makeManager();
        $practitioner = User::factory()->create(['name' => 'Dr. Amanuel']);
        $practitioner->assignRole('Practitioner');
        $service = $this->makeService();

        $this->makePaidVisit($practitioner, $service, 300);

        $this->actingAs($manager);

        Livewire::test(ReportIndex::class)
            ->call('setPreset', 'this_year')
            ->assertSee('Consultation')
            ->assertSee('Dr. Amanuel')
            ->assertSee('300.00 ETB');
    }

    public function test_new_patients_and_visits_and_prescriptions_are_counted(): void
    {
        $manager = $this->makeManager();
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $patient = $this->makePatient();

        $entry = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id]);
        $entry->update(['status' => 'completed', 'completed_at' => now()]);

        Prescription::create([
            'encounter_id' => $entry->encounter->id,
            'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id,
            'status' => 'dispensed',
            'dispensed_at' => now(),
        ]);

        $this->actingAs($manager);

        $html = Livewire::test(ReportIndex::class)->call('setPreset', 'today')->html();

        foreach (['New Patients', 'Visits Completed', 'Prescriptions Dispensed'] as $label) {
            preg_match('/'.preg_quote($label, '/').'<\/span>\s*<h4[^>]*>\s*(\d+)\s*<\/h4>/s', $html, $matches);
            $this->assertSame('1', $matches[1] ?? null, "Expected {$label} to show 1");
        }
    }

    public function test_csv_export_streams_a_download(): void
    {
        $manager = $this->makeManager();
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $service = $this->makeService();

        $this->makePaidVisit($practitioner, $service, 300);

        $this->actingAs($manager);

        Livewire::test(ReportIndex::class)
            ->call('setPreset', 'this_year')
            ->call('exportCsv')
            ->assertFileDownloaded();
    }
}
