<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'queue_entry_id', 'patient_id', 'practitioner_id',
    'patient_note', 'results',
    'status', 'finalized_at',
    'follow_up_date', 'follow_up_reason', 'follow_up_dismissed_at',
])]
class Encounter extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
            'follow_up_date' => 'date',
            'follow_up_dismissed_at' => 'datetime',
        ];
    }

    public function queueEntry()
    {
        return $this->belongsTo(QueueEntry::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function practitioner()
    {
        return $this->belongsTo(User::class, 'practitioner_id');
    }

    public function investigations()
    {
        return $this->belongsToMany(Investigation::class)->withPivot('price')->withTimestamps();
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    /**
     * Finalized encounters with a follow-up date that has arrived (or
     * passed), not yet dismissed. Doesn't account for whether the patient
     * has actually come back since - see hasPatientReturnedSinceFollowUp().
     */
    public function scopeNeedingFollowUp($query)
    {
        return $query->where('status', 'completed')
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', today())
            ->whereNull('follow_up_dismissed_at');
    }

    public function hasPatientReturnedSinceFollowUp(): bool
    {
        return $this->patient->queueEntries()
            ->where('check_in_time', '>', $this->created_at)
            ->exists();
    }

    /**
     * Encounters genuinely still awaiting a follow-up visit - due date has
     * passed, not dismissed, and the patient hasn't been seen again since.
     */
    public static function dueForFollowUp()
    {
        return static::with(['patient', 'practitioner'])
            ->needingFollowUp()
            ->orderBy('follow_up_date')
            ->get()
            ->reject(fn (Encounter $encounter) => $encounter->hasPatientReturnedSinceFollowUp())
            ->values();
    }
}
