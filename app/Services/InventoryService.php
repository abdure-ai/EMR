<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Medication;
use App\Models\Prescription;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for stock movement. Deductions always follow FEFO
 * (first-expired-first-out): the batch expiring soonest is drawn down first,
 * batches with no expiry date are treated as expiring last.
 */
class InventoryService
{
    public function receiveStock(Medication $medication, array $data, User $actor): InventoryBatch
    {
        return DB::transaction(function () use ($medication, $data, $actor) {
            $batch = $medication->batches()->create([
                'batch_number' => $data['batch_number'] ?? null,
                'quantity_received' => $data['quantity'],
                'quantity_remaining' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'received_at' => $data['received_at'] ?? now()->toDateString(),
                'received_by' => $actor->id,
            ]);

            StockMovement::create([
                'medication_id' => $medication->id,
                'inventory_batch_id' => $batch->id,
                'type' => 'received',
                'quantity_delta' => $data['quantity'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            return $batch;
        });
    }

    public function adjustBatch(InventoryBatch $batch, int $delta, string $reason, User $actor): void
    {
        DB::transaction(function () use ($batch, $delta, $reason, $actor) {
            $batch->update(['quantity_remaining' => max(0, $batch->quantity_remaining + $delta)]);

            StockMovement::create([
                'medication_id' => $batch->medication_id,
                'inventory_batch_id' => $batch->id,
                'type' => $delta < 0 ? 'wasted' : 'adjusted',
                'quantity_delta' => $delta,
                'notes' => $reason,
                'created_by' => $actor->id,
            ]);
        });
    }

    /**
     * Deducts stock for every catalog-linked item on the prescription, oldest
     * expiry first. Short falls aren't blocking - dispensing a herbal remedy
     * shouldn't be held hostage by imperfect stock bookkeeping - but they are
     * reported back so the pharmacist can act on them.
     *
     * @return array<int, string> human-readable shortfall messages, empty if none
     */
    public function deductForPrescription(Prescription $prescription, User $actor): array
    {
        $shortfalls = [];

        foreach ($prescription->items as $item) {
            if (! $item->medication_id) {
                continue;
            }

            $needed = $item->quantity ?: 1;
            $fulfilled = $this->deduct($item->medication_id, $needed, $actor, $prescription);

            if ($fulfilled < $needed) {
                $shortfalls[] = "{$item->name}: needed {$needed}, only {$fulfilled} in stock";
            }
        }

        return $shortfalls;
    }

    protected function deduct(int $medicationId, int $quantity, User $actor, Prescription $prescription): int
    {
        return DB::transaction(function () use ($medicationId, $quantity, $actor, $prescription) {
            $remaining = $quantity;

            $batches = InventoryBatch::where('medication_id', $medicationId)
                ->where('quantity_remaining', '>', 0)
                ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, $batch->quantity_remaining);
                $batch->update(['quantity_remaining' => $batch->quantity_remaining - $take]);

                StockMovement::create([
                    'medication_id' => $medicationId,
                    'inventory_batch_id' => $batch->id,
                    'prescription_id' => $prescription->id,
                    'type' => 'dispensed',
                    'quantity_delta' => -$take,
                    'created_by' => $actor->id,
                ]);

                $remaining -= $take;
            }

            return $quantity - $remaining;
        });
    }
}
