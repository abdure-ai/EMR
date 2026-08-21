<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'prescription_id', 'medication_id', 'custom_name',
    'dosage', 'frequency', 'duration', 'quantity', 'instructions',
])]
class PrescriptionItem extends Model
{
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }

    public function getNameAttribute(): string
    {
        return $this->medication?->name ?? $this->custom_name ?? 'Unspecified';
    }
}
