<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'department_id', 'name', 'duration_minutes', 'price', 'is_active',
    'description', 'image_path', 'show_on_website',
])]
class Service extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'show_on_website' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeShownOnWebsite($query)
    {
        return $query->where('is_active', true)->where('show_on_website', true);
    }
}
