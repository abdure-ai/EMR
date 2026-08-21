<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'appointment_id', 'patient_id', 'practitioner_id', 'token_number',
    'status', 'check_in_time', 'started_at', 'completed_at', 'created_by',
])]
class QueueEntry extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'check_in_time' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (QueueEntry $entry) {
            if (empty($entry->token_number)) {
                $entry->token_number = static::generateTokenNumber();
            }

            if (empty($entry->check_in_time)) {
                $entry->check_in_time = now();
            }
        });

        // Every visit gets a draft encounter to document right away - the
        // practitioner doesn't have to remember to create one.
        static::created(function (QueueEntry $entry) {
            Encounter::create([
                'queue_entry_id' => $entry->id,
                'patient_id' => $entry->patient_id,
                'practitioner_id' => $entry->practitioner_id,
                'status' => 'draft',
            ]);
        });
    }

    /**
     * Resets daily: "001", "002", ... derived from today's highest existing
     * token under a row lock. Same single-instance-MVP caveat as
     * Patient::generatePatientId().
     */
    public static function generateTokenNumber(): string
    {
        return DB::transaction(function () {
            $last = static::whereDate('check_in_time', today())
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $sequence = $last ? ((int) $last->token_number) + 1 : 1;

            return sprintf('%03d', $sequence);
        });
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function practitioner()
    {
        return $this->belongsTo(User::class, 'practitioner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function encounter()
    {
        return $this->hasOne(Encounter::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('check_in_time', today());
    }
}
