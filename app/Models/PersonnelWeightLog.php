<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelWeightLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'recorded_date',
        'height_cm',
        'weight_kg',
        'bmi',
        'bmi_category',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'recorded_date' => 'date',
            'height_cm' => 'decimal:1',
            'weight_kg' => 'decimal:1',
            'bmi' => 'decimal:2',
        ];
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    /**
     * Helper to calculate BMI and category
     */
    public static function calculateBmi(float $heightCm, float $weightKg): array
    {
        if ($heightCm <= 0 || $weightKg <= 0) {
            return ['bmi' => 0, 'category' => 'N/A'];
        }

        $heightM = $heightCm / 100;
        $bmi = round($weightKg / ($heightM * $heightM), 2);

        $category = match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25.0 => 'Normal',
            $bmi < 30.0 => 'Overweight',
            default => 'Obese',
        };

        return ['bmi' => $bmi, 'category' => $category];
    }
}
