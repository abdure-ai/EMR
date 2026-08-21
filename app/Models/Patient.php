<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'patient_id', 'first_name', 'middle_name', 'last_name', 'sex', 'date_of_birth', 'age', 'phone',
    'region', 'zone', 'woreda', 'kebele', 'house_no',
    'emergency_contact_name', 'emergency_contact_phone',
    'preferred_language', 'photo_url', 'id_document_ref', 'created_by',
])]
class Patient extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (empty($patient->patient_id)) {
                $patient->patient_id = static::generatePatientId();
            }
        });
    }

    /**
     * Assumption: NES-YYYY-NNNNNN, sequence reset per calendar year, derived from
     * the highest existing sequence for that year under a row lock. Good enough
     * for single-instance MVP traffic; revisit with a dedicated sequence table
     * before scaling to concurrent multi-instance writes.
     */
    public static function generatePatientId(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            // withTrashed(): a soft-deleted patient's number must not be reused.
            $last = static::withTrashed()
                ->whereYear('created_at', $year)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $sequence = $last ? ((int) substr($last->patient_id, -6)) + 1 : 1;

            return sprintf('NES-%d-%06d', $year, $sequence);
        });
    }

    public function medicalInfo()
    {
        return $this->hasOne(PatientMedicalInfo::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function queueEntries()
    {
        return $this->hasMany(QueueEntry::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function encounters()
    {
        return $this->hasMany(Encounter::class);
    }

    public function getFullNameAttribute(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->join(' ');
    }

    /**
     * Full structured address as a single display string, e.g.
     * "Bole, Woreda 03, Addis Ketema, Addis Ababa, House 42".
     */
    public function getFullAddressAttribute(): string
    {
        return collect([$this->kebele, $this->woreda, $this->zone, $this->region])
            ->filter()
            ->join(', ') ?: '';
    }
}
