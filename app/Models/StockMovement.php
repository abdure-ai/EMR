<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'medication_id', 'inventory_batch_id', 'prescription_id',
    'type', 'quantity_delta', 'notes', 'created_by',
])]
class StockMovement extends Model
{
    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
