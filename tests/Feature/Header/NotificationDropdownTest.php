<?php

namespace Tests\Feature\Header;

use App\Livewire\Header\NotificationDropdown;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationDropdownTest extends TestCase
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

    public function test_manager_sees_low_stock_alert(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');

        Medication::create(['name' => 'Paracetamol', 'form' => 'tablet', 'reorder_level' => 10, 'is_active' => true]);

        $this->actingAs($manager);

        Livewire::test(NotificationDropdown::class)
            ->assertSee('low on stock');
    }

    public function test_manager_sees_pending_invoice_alert(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Clinic Manager');
        $patient = $this->makePatient();

        Invoice::create(['patient_id' => $patient->id, 'type' => 'registration', 'status' => 'pending', 'total_amount' => 100]);

        $this->actingAs($manager);

        Livewire::test(NotificationDropdown::class)
            ->assertSee('awaiting payment');
    }

    public function test_practitioner_sees_only_their_own_waiting_patients(): void
    {
        $practitioner = User::factory()->create();
        $practitioner->assignRole('Practitioner');
        $otherPractitioner = User::factory()->create();
        $otherPractitioner->assignRole('Practitioner');

        $mine = $this->makePatient();
        QueueEntry::create(['patient_id' => $mine->id, 'practitioner_id' => $practitioner->id]);

        $theirs = $this->makePatient();
        QueueEntry::create(['patient_id' => $theirs->id, 'practitioner_id' => $otherPractitioner->id]);

        $this->actingAs($practitioner);

        Livewire::test(NotificationDropdown::class)
            ->assertSee('1 patient is waiting for you');
    }

    public function test_user_with_no_alerts_sees_all_caught_up(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $this->actingAs($cashier);

        Livewire::test(NotificationDropdown::class)
            ->assertSee("all caught up");
    }

    public function test_reception_does_not_see_inventory_alerts(): void
    {
        $reception = User::factory()->create();
        $reception->assignRole('Reception');

        Medication::create(['name' => 'Paracetamol', 'form' => 'tablet', 'reorder_level' => 10, 'is_active' => true]);

        $this->actingAs($reception);

        Livewire::test(NotificationDropdown::class)
            ->assertDontSee('low on stock');
    }
}
