<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'form', 'strength', 'price', 'reorder_level', 'is_active'])]
class Medication extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function currentStock(): int
    {
        return (int) $this->batches()->sum('quantity_remaining');
    }

    public function isLowStock(): bool
    {
        return $this->reorder_level !== null && $this->currentStock() <= $this->reorder_level;
    }
}
