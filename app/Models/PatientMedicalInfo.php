<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'patient_id', 'main_complaint', 'medical_history', 'allergies',
    'current_medications', 'previous_treatments', 'updated_by',
])]
class PatientMedicalInfo extends Model
{
    use Auditable;

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
