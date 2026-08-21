<?php

namespace Tests\Feature\Header;

use App\Livewire\Header\CommandPalette;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommandPaletteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => '+251900000'.random_int(100, 999),
        ], $overrides));
    }

    protected function makeManager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        return $manager;
    }

    public function test_empty_query_shows_module_shortcuts(): void
    {
        $this->actingAs($this->makeManager());

        Livewire::test(CommandPalette::class)
            ->assertSee('Patients')
            ->assertSee('Billing');
    }

    public function test_typing_finds_a_patient_by_name(): void
    {
        $this->makePatient(['first_name' => 'Zeynaba', 'phone' => '+251911112222']);

        $this->actingAs($this->makeManager());

        Livewire::test(CommandPalette::class)
            ->set('query', 'Zeynaba')
            ->assertSee('Zeynaba');
    }

    public function test_typing_finds_an_invoice_by_number(): void
    {
        $patient = $this->makePatient();
        $invoice = Invoice::create(['patient_id' => $patient->id, 'type' => 'registration', 'status' => 'pending', 'total_amount' => 100]);

        $this->actingAs($this->makeManager());

        Livewire::test(CommandPalette::class)
            ->set('query', $invoice->invoice_number)
            ->assertSee($invoice->invoice_number);
    }

    public function test_module_results_filter_by_typed_text(): void
    {
        $this->actingAs($this->makeManager());

        Livewire::test(CommandPalette::class)
            ->set('query', 'bill')
            ->assertSee('Billing')
            ->assertDontSee('Patients');
    }

    public function test_practitioner_search_only_finds_patients_checked_in_to_them(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $otherPractitioner = User::factory()->create();
        $otherPractitioner->assignRole('Practitioner');

        $mine = $this->makePatient(['first_name' => 'AssignedToMe']);
        QueueEntry::create(['patient_id' => $mine->id, 'practitioner_id' => $practitioner->id]);

        $notMine = $this->makePatient(['first_name' => 'NotAssigned']);

        $this->actingAs($practitioner);

        Livewire::test(CommandPalette::class)
            ->set('query', 'Assigned')
            ->assertSee('AssignedToMe')
            ->assertDontSee('NotAssigned');
    }
}
