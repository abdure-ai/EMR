<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'patient_id', 'service_id', 'practitioner_id', 'scheduled_at',
    'status', 'source', 'notes', 'created_by',
])]
class Appointment extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function practitioner()
    {
        return $this->belongsTo(User::class, 'practitioner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function queueEntry()
    {
        return $this->hasOne(QueueEntry::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }
}
