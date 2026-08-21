<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['new_patient_card_fee', 'revisit_free_within_days', 'expiry_alert_days'])]
class ClinicSetting extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'new_patient_card_fee' => 'decimal:2',
            'revisit_free_within_days' => 'integer',
            'expiry_alert_days' => 'integer',
        ];
    }

    /**
     * Singleton row (id=1). Created on first access with the column defaults
     * from the migration if it doesn't exist yet.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
