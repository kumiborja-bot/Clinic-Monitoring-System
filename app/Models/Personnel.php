<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';

    protected $fillable = [
        'employee_id', 'name', 'email', 'birthdate', 'address', 'age', 'sex',
        'civil_status', 'religion', 'department_id', 'position', 'contact_no',
        'emergency_contact_person', 'emergency_contact_relationship',
        'emergency_contact_address', 'emergency_contact_no', 'photo',
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

    public function personnelDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PersonnelDocument::class);
    }
}
