<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for clinic billing at two distinct moments:
 *
 * 1. registerAndCheckIn() - a brand-new patient's one-off card fee and
 *    their first visit's service fee, combined into a single invoice so
 *    the cashier collects one payment, not two. Paying it is what creates
 *    the queue entry (same as checkIn()).
 * 2. checkIn() - the per-visit service fee alone, charged when an
 *    already-registered patient checks in for an appointment or a
 *    revisit. Paying THIS is what creates the queue entry.
 *
 * Keeping both here (rather than duplicated across Livewire components)
 * means the billing rule only lives in one place.
 */
class CheckInService
{
    /**
     * Registration + first-visit invoice, combined. Falls back to the
     * card-fee-only or visit-fee-only shape naturally: if one of the two
     * fees is zero, only the other's line item is added; if both are
     * zero, the patient is queued directly with no invoice at all - same
     * "nothing owed" behavior as checkIn() alone.
     *
     * @return array{queueEntry: ?QueueEntry, invoice: ?Invoice}
     */
    public function registerAndCheckIn(Patient $patient, User $practitioner, Service $service, User $actor): array
    {
        $cardFee = (float) ClinicSetting::current()->new_patient_card_fee;

        $lineItems = $cardFee > 0
            ? [['description' => 'New Patient Registration Card', 'amount' => $cardFee]]
            : [];

        $lineItems = array_merge($lineItems, $this->priceLineItems($patient, $service));
        $total = array_sum(array_column($lineItems, 'amount'));

        if ($total <= 0) {
            $queueEntry = QueueEntry::create([
                'patient_id' => $patient->id,
                'practitioner_id' => $practitioner->id,
                'created_by' => $actor->id,
            ]);

            return ['queueEntry' => $queueEntry, 'invoice' => null];
        }

        $invoice = DB::transaction(function () use ($patient, $practitioner, $service, $actor, $lineItems, $total) {
            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'type' => 'visit',
                'practitioner_id' => $practitioner->id,
                'service_id' => $service->id,
                'status' => 'pending',
                'total_amount' => $total,
                'created_by' => $actor->id,
            ]);

            foreach ($lineItems as $item) {
                $invoice->lineItems()->create($item);
            }

            return $invoice;
        });

        return ['queueEntry' => null, 'invoice' => $invoice];
    }

    /**
     * A visit is free if it falls within ClinicSetting::revisit_free_within_days
     * of the patient's last completed visit (this also naturally covers a
     * patient's very first-ever check-in when the service has no price set).
     */
    public function priceLineItems(Patient $patient, Service $service): array
    {
        $settings = ClinicSetting::current();

        $lastCompletedVisit = QueueEntry::where('patient_id', $patient->id)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->first();

        if ($lastCompletedVisit) {
            $daysSinceLastVisit = $lastCompletedVisit->completed_at->diffInDays(now());

            if ($daysSinceLastVisit <= $settings->revisit_free_within_days) {
                return [];
            }
        }

        if ($service->price) {
            return [['description' => $service->name, 'amount' => (float) $service->price]];
        }

        return [];
    }

    /**
     * Either queues the patient directly (nothing owed) or creates a pending
     * 'visit' invoice for the cashier to process. Exactly one of the two
     * return values is non-null.
     *
     * @return array{queueEntry: ?QueueEntry, invoice: ?Invoice}
     */
    public function checkIn(Patient $patient, User $practitioner, Service $service, ?Appointment $appointment, User $actor): array
    {
        $lineItems = $this->priceLineItems($patient, $service);
        $total = array_sum(array_column($lineItems, 'amount'));

        if ($total <= 0) {
            $queueEntry = QueueEntry::create([
                'appointment_id' => $appointment?->id,
                'patient_id' => $patient->id,
                'practitioner_id' => $practitioner->id,
                'created_by' => $actor->id,
            ]);

            $appointment?->update(['status' => 'checked_in']);

            return ['queueEntry' => $queueEntry, 'invoice' => null];
        }

        $invoice = DB::transaction(function () use ($patient, $practitioner, $service, $appointment, $actor, $lineItems, $total) {
            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'appointment_id' => $appointment?->id,
                'type' => 'visit',
                'practitioner_id' => $practitioner->id,
                'service_id' => $service->id,
                'status' => 'pending',
                'total_amount' => $total,
                'created_by' => $actor->id,
            ]);

            foreach ($lineItems as $item) {
                $invoice->lineItems()->create($item);
            }

            $appointment?->update(['status' => 'awaiting_payment']);

            return $invoice;
        });

        return ['queueEntry' => null, 'invoice' => $invoice];
    }

    /**
     * Cashier approves payment. For a 'visit' invoice, the patient only
     * actually joins the practitioner's queue now. A 'registration' invoice
     * has no practitioner to queue for - it's just marked paid.
     */
    public function processPayment(Invoice $invoice, string $method, User $actor): ?QueueEntry
    {
        return DB::transaction(function () use ($invoice, $method, $actor) {
            $invoice->payments()->create([
                'amount' => $invoice->total_amount,
                'method' => $method,
                'recorded_by' => $actor->id,
            ]);

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'processed_by' => $actor->id,
            ]);

            if ($invoice->type !== 'visit' || ! $invoice->practitioner_id) {
                return null;
            }

            $queueEntry = QueueEntry::create([
                'appointment_id' => $invoice->appointment_id,
                'patient_id' => $invoice->patient_id,
                'practitioner_id' => $invoice->practitioner_id,
                'created_by' => $actor->id,
            ]);

            $invoice->appointment?->update(['status' => 'checked_in']);

            return $queueEntry;
        });
    }
}
