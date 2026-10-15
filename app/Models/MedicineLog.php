<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MedicineLog extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Every dispensing entry contributes exactly once to the stock card,
        // including consultation, treatment, and offline dispensing entries.
        static::created(function (MedicineLog $log) {
            MedicineStockMovement::create([
                'medicine_id' => $log->medicine_id,
                'transaction_date' => $log->dispensed_at->toDateString(),
                'type' => 'out',
                'quantity' => $log->quantity,
                'supplier' => null,
                'requester' => $log->patient?->name ?? 'Unknown',
                'notes' => $log->notes,
            ]);
        });
    }

    protected $fillable = [
        'medicine_id', 'treatment_id', 'appointment_id', 'patient_type', 'patient_id',
        'quantity', 'dispensed_by', 'dispensed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): MorphTo
    {
        return $this->morphTo();
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
