<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Personnel;
use App\Models\PersonnelWeightLog;
use App\Models\MedicalRecord;
use Illuminate\Database\Seeder;

class PersonnelWeightLogSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::firstOrCreate(
            ['code' => 'ADMIN'],
            ['name' => 'Administration', 'type' => 'personnel']
        );

        $personnel = Personnel::firstOrCreate(
            ['employee_id' => 'EMP-2026-001'],
            [
                'name' => 'Captain John Doe',
                'department_id' => $dept->id,
                'position' => 'Senior Maritime Instructor',
                'sex' => 'Male',
                'age' => 38,
                'contact_no' => '09171234567',
            ]
        );

        // Clear existing weight logs for test personnel
        PersonnelWeightLog::where('personnel_id', $personnel->id)->delete();

        // 1. Previous Month (July 2026): Height 170cm, Weight 85kg => BMI 29.41 (Overweight)
        $log1 = PersonnelWeightLog::create([
            'personnel_id' => $personnel->id,
            'recorded_date' => '2026-07-01',
            'height_cm' => 170.0,
            'weight_kg' => 85.0,
            'bmi' => 29.41,
            'bmi_category' => 'Overweight',
            'remarks' => 'Monthly Health Check - High BMI flagged for diet modification',
        ]);

        // 2. Current Month (August 2026): Height 170cm, Weight 70kg => BMI 24.22 (Normal)
        $log2 = PersonnelWeightLog::create([
            'personnel_id' => $personnel->id,
            'recorded_date' => '2026-08-01',
            'height_cm' => 170.0,
            'weight_kg' => 70.0,
            'bmi' => 24.22,
            'bmi_category' => 'Normal',
            'remarks' => 'Monthly Health Check - Weight normalized following fitness plan',
        ]);

        // Synchronize current medical record to reflect the latest reading
        MedicalRecord::updateOrCreate(
            ['recordable_type' => Personnel::class, 'recordable_id' => $personnel->id],
            [
                'height_cm' => 170.0,
                'weight_kg' => 70.0,
                'bmi' => 24.22,
                'bmi_category' => 'Normal',
                'examination_date' => '2026-08-01',
            ]
        );
    }
}
