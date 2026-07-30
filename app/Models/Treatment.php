<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_type', 'patient_id', 'diagnosis',
        'treatment_given', 'medication_given', 'medicine_id', 'medicine_quantity',
        'appointment_id', 'treated_by', 'treated_at', 'notes', 'status', 'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'treated_at' => 'datetime',
            'follow_up_date' => 'date:Y-m-d',
        ];
    }

    public function patient(): MorphTo
    {
        return $this->morphTo();
    }

    public function treatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treated_by');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function medicineLogs(): HasMany
    {
        return $this->hasMany(MedicineLog::class, 'treatment_id');
    }
}
