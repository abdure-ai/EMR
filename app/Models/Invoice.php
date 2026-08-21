<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'invoice_number', 'patient_id', 'appointment_id', 'type', 'practitioner_id', 'service_id',
    'status', 'total_amount', 'created_by', 'processed_by', 'paid_at',
])]
class Invoice extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    /**
     * Same scheme/caveats as Patient::generatePatientId(): INV-YYYY-NNNNNN,
     * sequence reset per calendar year, derived under a row lock. Fine for
     * single-instance MVP traffic.
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $last = static::whereYear('created_at', $year)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $sequence = $last ? ((int) substr($last->invoice_number, -6)) + 1 : 1;

            return sprintf('INV-%d-%06d', $year, $sequence);
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function practitioner()
    {
        return $this->belongsTo(User::class, 'practitioner_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
