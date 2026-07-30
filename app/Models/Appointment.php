<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_type',
        'patient_id',
        'email',
        'appointment_date',
        'clinic_location',
        'assigned_provider_id',
        'assigned_provider_name',
        'reason_for_visit',
        'status',
        'cancellation_reason',
        'notes',
        'reminder_sent',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
            'reminder_sent' => 'boolean',
        ];
    }

    public function patient(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_provider_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function medicineLogs(): HasMany
    {
        return $this->hasMany(MedicineLog::class);
    }
}
