<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'medication_id', 'batch_number', 'quantity_received', 'quantity_remaining',
    'unit_cost', 'expiry_date', 'received_at', 'received_by',
])]
class InventoryBatch extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'expiry_date' => 'date',
            'received_at' => 'date',
        ];
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $withinDays): bool
    {
        return $this->expiry_date
            && ! $this->isExpired()
            && $this->expiry_date->lte(now()->addDays($withinDays));
    }
}
