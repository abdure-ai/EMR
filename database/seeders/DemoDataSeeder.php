<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Investigation;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Fills in the modules that real day-to-day testing left completely empty:
 * inventory batches, appointments, and prescriptions. Also adds two more
 * practitioners so practitioner-scoped views (Reports "by practitioner",
 * the practitioner-only patient list, etc.) have more than one to look at.
 * Safe to re-run: everything keyed off firstOrCreate or a fixed random seed
 * range, so it won't pile up duplicates on a second pass.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $practitioners = $this->makePractitioners();
        $this->seedInventory();
        $this->seedAppointments($practitioners);
        $this->seedPrescriptions($practitioners);
    }

    protected function makePractitioners(): \Illuminate\Support\Collection
    {
        $extra = [
            ['name' => 'Tewodros Alemu', 'email' => 'practitioner2@nesiha.test'],
            ['name' => 'Ruth Getachew', 'email' => 'practitioner3@nesiha.test'],
        ];

        foreach ($extra as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make('password'), 'email_verified_at' => now(), 'is_active' => true]
            );
            $user->syncRoles(['Practitioner']);
        }

        return User::role('Practitioner')->orderBy('id')->get();
    }

    protected function seedInventory(): void
    {
        $service = app(InventoryService::class);
        $pharmacist = User::where('email', 'pharmacist@nesiha.test')->first();
        $medications = Medication::all();

        // Cycle through a fixed set of realistic scenarios so the Inventory
        // list, low-stock/expiring notification alerts, and FEFO deduction
        // all have real variety instead of every medication looking the same.
        $scenarios = [
            ['quantity' => 200, 'expiry_days' => 400], // healthy stock, far expiry
            ['quantity' => 5, 'expiry_days' => 400],    // low stock
            ['quantity' => 60, 'expiry_days' => 15],    // expiring soon
            ['quantity' => 40, 'expiry_days' => -5],    // already expired
        ];

        foreach ($medications as $i => $medication) {
            if ($medication->batches()->exists()) {
                continue;
            }

            $scenario = $scenarios[$i % count($scenarios)];

            $service->receiveStock($medication, [
                'batch_number' => 'B-'.strtoupper(substr(md5($medication->id.'-1'), 0, 6)),
                'quantity' => $scenario['quantity'],
                'unit_cost' => round(($medication->price ?: 20) * 0.4, 2),
                'expiry_date' => now()->addDays($scenario['expiry_days'])->toDateString(),
                'received_at' => now()->subDays(rand(5, 60))->toDateString(),
            ], $pharmacist);

            // A second, later-expiring batch only for the "healthy" and
            // "expiring soon" scenarios - so FEFO deduction has more than
            // one batch to choose between, without topping the "low stock"
            // and "expired" scenarios back up to a healthy total.
            if ($i % 4 === 0 || $i % 4 === 2) {
                $service->receiveStock($medication, [
                    'batch_number' => 'B-'.strtoupper(substr(md5($medication->id.'-2'), 0, 6)),
                    'quantity' => 100,
                    'unit_cost' => round(($medication->price ?: 20) * 0.4, 2),
                    'expiry_date' => now()->addDays(300)->toDateString(),
                    'received_at' => now()->subDays(rand(1, 10))->toDateString(),
                ], $pharmacist);
            }
        }
    }

    protected function seedAppointments($practitioners): void
    {
        if (Appointment::exists()) {
            return;
        }

        $reception = User::where('email', 'reception@nesiha.test')->first();
        $patients = Patient::inRandomOrder()->limit(18)->get();
        $services = Service::all();

        // [offsetDays, status] pairs - plausible statuses for that point in
        // time (past visits resolved one way or another, today mid-flow,
        // future still just booked/confirmed).
        $slots = [
            [-6, 'completed'], [-5, 'completed'], [-4, 'no_show'], [-3, 'completed'],
            [-2, 'cancelled'], [-1, 'completed'], [0, 'checked_in'], [0, 'awaiting_payment'],
            [1, 'confirmed'], [1, 'booked'], [2, 'booked'], [3, 'confirmed'],
            [4, 'booked'], [5, 'booked'], [6, 'confirmed'], [7, 'booked'],
        ];

        $i = 0;
        foreach ($slots as [$offsetDays, $status]) {
            $patient = $patients[$i % $patients->count()] ?? null;
            if (! $patient) {
                break;
            }

            Appointment::create([
                'patient_id' => $patient->id,
                'service_id' => $services->random()->id,
                'practitioner_id' => $practitioners->random()->id,
                'scheduled_at' => today()->addDays($offsetDays)->setTime(9 + ($i % 7), $i % 2 === 0 ? 0 : 30),
                'status' => $status,
                'source' => ['website', 'phone', 'walk-in', 'referral'][$i % 4],
                'created_by' => $reception?->id,
            ]);

            $i++;
        }
    }

    protected function seedPrescriptions($practitioners): void
    {
        if (Prescription::exists()) {
            return;
        }

        $inventory = app(InventoryService::class);
        $pharmacist = User::where('email', 'pharmacist@nesiha.test')->first();
        $patients = Patient::inRandomOrder()->limit(6)->get();
        $medications = Medication::all();
        $investigations = Investigation::where('is_active', true)->inRandomOrder()->limit(3)->get();

        $complaints = ['Chronic joint pain', 'Digestive discomfort', 'Fatigue and low energy', 'Skin irritation', 'Sleep difficulty', 'Seasonal allergies'];

        foreach ($patients as $i => $patient) {
            $practitioner = $practitioners[$i % $practitioners->count()];

            $daysAgo = 3 + $i * 5;
            $entry = QueueEntry::create([
                'patient_id' => $patient->id,
                'practitioner_id' => $practitioner->id,
                'status' => 'completed',
                'check_in_time' => now()->subDays($daysAgo)->setTime(9, 0),
                'started_at' => now()->subDays($daysAgo)->setTime(9, 10),
                'completed_at' => now()->subDays($daysAgo)->setTime(9, 30),
            ]);

            $encounter = $entry->encounter;
            $followUpDue = $i % 3 === 0; // a few genuinely overdue, for Follow-ups

            $encounter->update([
                'patient_note' => $complaints[$i % count($complaints)].' - reviewed and treated.',
                'results' => 'Vitals stable. Advised herbal regimen and follow-up as scheduled.',
                'status' => 'completed',
                'finalized_at' => now()->subDays($daysAgo)->setTime(9, 30),
                'follow_up_date' => $followUpDue ? now()->subDays(2) : now()->addDays(14),
                'follow_up_reason' => 'Reassess response to treatment',
            ]);

            if ($investigations->isNotEmpty()) {
                $investigation = $investigations[$i % $investigations->count()];
                $encounter->investigations()->attach($investigation->id, ['price' => $investigation->price]);
            }

            $prescription = Prescription::create([
                'encounter_id' => $encounter->id,
                'patient_id' => $patient->id,
                'practitioner_id' => $practitioner->id,
                'status' => 'pending',
            ]);

            $medA = $medications[$i % $medications->count()];
            $prescription->items()->create([
                'medication_id' => $medA->id,
                'dosage' => '1 tablet', 'frequency' => 'Twice daily', 'duration' => '7 days',
                'quantity' => 14, 'instructions' => 'Take after meals.',
            ]);
            $prescription->items()->create([
                'custom_name' => 'Warm compress',
                'dosage' => null, 'frequency' => 'Once daily', 'duration' => '5 days',
                'quantity' => null, 'instructions' => 'Apply to affected area for 15 minutes.',
            ]);

            // Dispense every other one so Prescriptions/Inventory movements
            // show both pending and completed states.
            if ($i % 2 === 0) {
                $inventory->deductForPrescription($prescription, $pharmacist);
                $prescription->update([
                    'status' => 'dispensed',
                    'dispensed_at' => now()->subDays($daysAgo)->addHour(),
                    'dispensed_by' => $pharmacist?->id,
                ]);
            }
        }
    }
}
