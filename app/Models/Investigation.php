<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category', 'subcategory', 'name', 'price', 'is_active'])]
class Investigation extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function encounters()
    {
        return $this->belongsToMany(Encounter::class)->withPivot('price')->withTimestamps();
    }
}
