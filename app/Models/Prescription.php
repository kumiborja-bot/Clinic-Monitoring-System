<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'patient_type',
        'patient_id',
        'prescribed_by',
        'prescriber_name',
        'medication_details',
        'notes',
        'issued_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'medication_details' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): MorphTo
    {
        return $this->morphTo();
    }

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }
}
