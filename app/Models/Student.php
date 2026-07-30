<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id_number', 'name', 'email', 'birthdate', 'address', 'age',
        'sex', 'civil_status', 'religion', 'department_id', 'course_id',
        'year_level', 'contact_no',
        'emergency_contact_person', 'emergency_contact_relationship',
        'emergency_contact_address', 'emergency_contact_no',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function medicalRecord(): MorphOne
    {
        return $this->morphOne(MedicalRecord::class, 'recordable');
    }

    public function treatments(): MorphMany
    {
        return $this->morphMany(Treatment::class, 'patient');
    }

    public function medicineLogs(): MorphMany
    {
        return $this->morphMany(MedicineLog::class, 'patient');
    }

    public function appointments(): MorphMany
    {
        return $this->morphMany(Appointment::class, 'patient');
    }

    public function prescriptions(): MorphMany
    {
        return $this->morphMany(Prescription::class, 'patient');
    }

    public function maritimeDocuments(): HasMany
    {
        return $this->hasMany(MaritimeDocument::class);
    }

    public function isMaritime(): bool
    {
        return $this->department?->code === 'CME';
    }
}
