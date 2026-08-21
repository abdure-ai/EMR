<?php

namespace Tests\Feature\Inventory;

use App\Livewire\Inventory\InventoryIndex;
use App\Livewire\Inventory\MedicationInventory;
use App\Livewire\Inventory\StockMovementIndex;
use App\Models\ClinicSetting;
use App\Models\InventoryBatch;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\QueueEntry;
use App\Models\User;
use App\Services\InventoryService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makePharmacist(): User
    {
        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('Pharmacist');

        return $pharmacist;
    }

    protected function makePractitioner(): User
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');

        return $practitioner;
    }

    protected function makePatient(string $phone = '+251900000001'): Patient
    {
        return Patient::create([
            'first_name' => 'Sara', 'last_name' => 'Ibrahim', 'sex' => 'female',
            'age' => 30, 'phone' => $phone,
        ]);
    }

    public function test_pharmacist_can_receive_stock(): void
    {
        $pharmacist = $this->makePharmacist();
        $medication = Medication::create(['name' => 'Black Seed Oil']);

        $this->actingAs($pharmacist);

        Livewire::test(MedicationInventory::class, ['medication' => $medication])
            ->set('quantity', '50')
            ->set('expiry_date', now()->addMonths(6)->toDateString())
            ->set('received_at', now()->toDateString())
            ->call('receiveStock')
            ->assertHasNoErrors();

        $this->assertSame(50, $medication->currentStock());
        $this->assertDatabaseHas('stock_movements', ['medication_id' => $medication->id, 'type' => 'received', 'quantity_delta' => 50]);
    }

    public function test_pharmacist_can_adjust_a_batch_for_wastage(): void
    {
        $pharmacist = $this->makePharmacist();
        $medication = Medication::create(['name' => 'Honey Elixir']);
        $batch = InventoryBatch::create([
            'medication_id' => $medication->id, 'quantity_received' => 20, 'quantity_remaining' => 20,
            'received_at' => now()->toDateString(),
        ]);

        $this->actingAs($pharmacist);

        Livewire::test(MedicationInventory::class, ['medication' => $medication])
            ->call('startAdjusting', $batch->id)
            ->set('adjustDelta', '-5')
            ->set('adjustReason', 'Bottle broke')
            ->call('adjustStock')
            ->assertHasNoErrors();

        $this->assertSame(15, $batch->fresh()->quantity_remaining);
        $this->assertDatabaseHas('stock_movements', ['inventory_batch_id' => $batch->id, 'type' => 'wasted', 'quantity_delta' => -5]);
    }

    public function test_dispensing_a_prescription_deducts_stock_fefo(): void
    {
        $pharmacist = $this->makePharmacist();
        $practitioner = $this->makePractitioner();
        $patient = $this->makePatient();
        $medication = Medication::create(['name' => 'Ginger Capsules']);

        // Older batch expires sooner - should be drawn down first.
        $oldBatch = InventoryBatch::create([
            'medication_id' => $medication->id, 'quantity_received' => 5, 'quantity_remaining' => 5,
            'expiry_date' => now()->addDays(10), 'received_at' => now()->subDays(30)->toDateString(),
        ]);
        $newBatch = InventoryBatch::create([
            'medication_id' => $medication->id, 'quantity_received' => 20, 'quantity_remaining' => 20,
            'expiry_date' => now()->addDays(90), 'received_at' => now()->toDateString(),
        ]);

        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;
        $prescription = Prescription::create([
            'encounter_id' => $encounter->id, 'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id, 'status' => 'pending',
        ]);
        $prescription->items()->create(['medication_id' => $medication->id, 'quantity' => 8]);

        app(InventoryService::class)->deductForPrescription($prescription, $pharmacist);

        // 5 from the sooner-expiring batch, 3 from the other.
        $this->assertSame(0, $oldBatch->fresh()->quantity_remaining);
        $this->assertSame(17, $newBatch->fresh()->quantity_remaining);
        $this->assertSame(2, $medication->stockMovements()->where('type', 'dispensed')->count());
    }

    public function test_dispensing_reports_a_shortfall_without_blocking(): void
    {
        $pharmacist = $this->makePharmacist();
        $practitioner = $this->makePractitioner();
        $patient = $this->makePatient();
        $medication = Medication::create(['name' => 'Chamomile Tea']);
        InventoryBatch::create([
            'medication_id' => $medication->id, 'quantity_received' => 2, 'quantity_remaining' => 2,
            'received_at' => now()->toDateString(),
        ]);

        $encounter = QueueEntry::create(['patient_id' => $patient->id, 'practitioner_id' => $practitioner->id])->encounter;
        $prescription = Prescription::create([
            'encounter_id' => $encounter->id, 'patient_id' => $patient->id,
            'practitioner_id' => $practitioner->id, 'status' => 'pending',
        ]);
        $prescription->items()->create(['medication_id' => $medication->id, 'quantity' => 5]);

        $shortfalls = app(InventoryService::class)->deductForPrescription($prescription, $pharmacist);

        $this->assertCount(1, $shortfalls);
        $this->assertSame(0, $medication->currentStock());
    }

    public function test_low_stock_medication_is_flagged(): void
    {
        $pharmacist = $this->makePharmacist();
        $medication = Medication::create(['name' => 'Fenugreek Powder', 'reorder_level' => 10]);
        InventoryBatch::create([
            'medication_id' => $medication->id, 'quantity_received' => 5, 'quantity_remaining' => 5,
            'received_at' => now()->toDateString(),
        ]);

        $this->assertTrue($medication->isLowStock());

        $this->actingAs($pharmacist);

        Livewire::test(InventoryIndex::class)
            ->set('status', 'low')
            ->assertSee('Fenugreek Powder');
    }

    public function test_expiring_batch_is_flagged(): void
    {
        $pharmacist = $this->makePharmacist();
        ClinicSetting::current()->update(['expiry_alert_days' => 30]);
        $medication = Medication::create(['name' => 'Senna Tablets']);
        InventoryBatch::create([
            'medication_id' => $medication->id, 'quantity_received' => 10, 'quantity_remaining' => 10,
            'expiry_date' => now()->addDays(5), 'received_at' => now()->toDateString(),
        ]);

        $this->actingAs($pharmacist);

        Livewire::test(InventoryIndex::class)
            ->set('status', 'expiring')
            ->assertSee('Senna Tablets');
    }

    public function test_stock_ledger_lists_movements(): void
    {
        $pharmacist = $this->makePharmacist();
        $medication = Medication::create(['name' => 'Paracetamol']);
        app(InventoryService::class)->receiveStock($medication, ['quantity' => 100, 'received_at' => now()->toDateString()], $pharmacist);

        $this->actingAs($pharmacist);

        Livewire::test(StockMovementIndex::class)
            ->assertSee('Paracetamol')
            ->assertSee('Received');
    }

    public function test_reception_cannot_access_inventory(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        $this->actingAs($reception)
            ->get(route('inventory.index'))
            ->assertForbidden();
    }

    public function test_practitioner_cannot_access_inventory(): void
    {
        $practitioner = $this->makePractitioner();

        $this->actingAs($practitioner)
            ->get(route('inventory.index'))
            ->assertForbidden();
    }
}
