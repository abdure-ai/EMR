<?php

namespace App\Livewire\Header;

use App\Models\ClinicSetting;
use App\Models\Encounter;
use App\Models\Invoice;
use App\Models\Medication;
use App\Models\QueueEntry;
use Livewire\Component;

class NotificationDropdown extends Component
{
    /**
     * Live status counts, not a stored/dismissible inbox - same philosophy
     * as Follow-ups: derive from real data on every render rather than
     * tracking read/unread state that could drift out of sync.
     */
    public function render()
    {
        $user = auth()->user();
        $items = [];

        if ($user->can('inventory.view')) {
            $lowStockCount = Medication::query()
                ->whereNotNull('reorder_level')
                ->whereRaw('(select coalesce(sum(ib.quantity_remaining), 0) from inventory_batches ib where ib.medication_id = medications.id) <= medications.reorder_level')
                ->count();

            if ($lowStockCount > 0) {
                $items[] = [
                    'icon' => 'inventory',
                    'message' => $lowStockCount === 1 ? '1 medication is low on stock' : "{$lowStockCount} medications are low on stock",
                    'count' => $lowStockCount,
                    'url' => route('inventory.index', ['status' => 'low']),
                ];
            }

            $expiryAlertDays = ClinicSetting::current()->expiry_alert_days;
            $expiringCount = Medication::query()
                ->whereHas('batches', fn ($q) => $q->where('quantity_remaining', '>', 0)
                    ->whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($expiryAlertDays)->toDateString()]))
                ->count();

            if ($expiringCount > 0) {
                $items[] = [
                    'icon' => 'expiry',
                    'message' => $expiringCount === 1 ? '1 medication has batches expiring soon' : "{$expiringCount} medications have batches expiring soon",
                    'count' => $expiringCount,
                    'url' => route('inventory.index', ['status' => 'expiring']),
                ];
            }
        }

        if ($user->can('followups.view')) {
            $dueCount = Encounter::dueForFollowUp()->count();

            if ($dueCount > 0) {
                $items[] = [
                    'icon' => 'follow-up',
                    'message' => $dueCount === 1 ? '1 follow-up is due' : "{$dueCount} follow-ups are due",
                    'count' => $dueCount,
                    'url' => route('follow-ups.index'),
                ];
            }
        }

        if ($user->can('billing.view')) {
            $pendingCount = Invoice::where('status', 'pending')->count();

            if ($pendingCount > 0) {
                $items[] = [
                    'icon' => 'billing',
                    'message' => $pendingCount === 1 ? '1 invoice is awaiting payment' : "{$pendingCount} invoices are awaiting payment",
                    'count' => $pendingCount,
                    'url' => route('billing.index'),
                ];
            }
        }

        if ($user->hasRole('Practitioner')) {
            $waitingCount = QueueEntry::where('practitioner_id', $user->id)
                ->whereIn('status', ['waiting', 'with_practitioner'])
                ->count();

            if ($waitingCount > 0) {
                $items[] = [
                    'icon' => 'queue',
                    'message' => $waitingCount === 1 ? '1 patient is waiting for you' : "{$waitingCount} patients are waiting for you",
                    'count' => $waitingCount,
                    'url' => route('patients.index'),
                ];
            }
        }

        return view('livewire.header.notification-dropdown', [
            'items' => $items,
            'total' => array_sum(array_column($items, 'count')),
        ]);
    }
}
