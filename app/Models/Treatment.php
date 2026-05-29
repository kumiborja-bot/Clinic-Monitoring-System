<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_type', 'patient_id', 'diagnosis',
        'treatment_given', 'medication_given', 'treated_by',
        'treated_at', 'notes', 'status', 'follow_up_date',
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
}
