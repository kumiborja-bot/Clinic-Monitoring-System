<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Course;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            // Student/Academic Departments
            [
                'code' => 'CTEAS',
                'name' => 'CTEAS',
                'full_name' => 'College of Teacher Education Arts and Sciences',
                'type' => 'student',
                'courses' => ['Education', 'Psychology'],
            ],
            [
                'code' => 'CCLS',
                'name' => 'CCLS',
                'full_name' => 'College of Computing and Library Studies',
                'type' => 'student',
                'courses' => ['Information System', 'Library Information System'],
            ],
            [
                'code' => 'CBTM',
                'name' => 'CBTM',
                'full_name' => 'College of Business and Tourism Management',
                'type' => 'student',
                'courses' => ['Tourism', 'Entrepreneur'],
            ],
            [
                'code' => 'CME',
                'name' => 'CME',
                'full_name' => 'College of Maritime Education',
                'type' => 'student',
                'courses' => ['Transportation', 'Engineering'],
            ],
            [
                'code' => 'CCJE',
                'name' => 'CCJE',
                'full_name' => 'College of Criminal Justice Education',
                'type' => 'student',
                'courses' => [],
            ],
            [
                'code' => 'BSED',
                'name' => 'Basic Education',
                'full_name' => 'Basic Education',
                'type' => 'student',
                'courses' => ['Senior High School', 'Junior High School'],
            ],

            // Personnel/Non-Academic Departments
            [
                'code' => 'HRD',
                'name' => 'Human Resource Department',
                'full_name' => 'Human Resource Development and Management',
                'type' => 'personnel',
                'courses' => [],
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance',
                'full_name' => 'Finance and Accounting Office',
                'type' => 'personnel',
                'courses' => [],
            ],
            [
                'code' => 'ADMIN',
                'name' => 'Administration',
                'full_name' => 'Administrative and Support Services',
                'type' => 'personnel',
                'courses' => [],
            ],
            [
                'code' => 'MAINT',
                'name' => 'Maintenance',
                'full_name' => 'Maintenance and Physical Plant',
                'type' => 'personnel',
                'courses' => [],
            ],
            [
                'code' => 'HEALTH',
                'name' => 'Health Services',
                'full_name' => 'Medical Clinic and Health Services',
                'type' => 'personnel',
                'courses' => [],
            ],
            [
                'code' => 'FAC',
                'name' => 'Faculty',
                'full_name' => 'Academic Faculty and Instruction',
                'type' => 'personnel',
                'courses' => [],
            ],
            [
                'code' => 'IT',
                'name' => 'IT Support',
                'full_name' => 'Information Technology and Systems Office',
                'type' => 'personnel',
                'courses' => [],
            ],
        ];

        foreach ($departments as $deptData) {
            $courses = $deptData['courses'];
            unset($deptData['courses']);

            // Find existing by code or create
            $department = Department::updateOrCreate(
                ['code' => $deptData['code']],
                $deptData
            );

            foreach ($courses as $courseName) {
                Course::firstOrCreate([
                    'department_id' => $department->id,
                    'name' => $courseName,
                ]);
            }
        }
    }
}
