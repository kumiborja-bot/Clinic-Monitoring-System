<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\Personnel;
use App\Models\Student;
use App\Models\Treatment;
use App\Services\MedicineStockCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $topDiagnoses =
            Treatment::select(
                'diagnosis',
                DB::raw(
                    'count(*) as total'
                )
            )
            ->whereNotNull(
                'diagnosis'
            )
            ->groupBy(
                'diagnosis'
            )
            ->orderByDesc(
                'total'
            )
            ->limit(7)
            ->get();

        $treatmentTrends =
            Treatment::select(
                DB::raw(
                    'DATE(treated_at) as date'
                ),
                DB::raw(
                    'count(*) as total'
                )
            )
            ->whereNotNull(
                'treated_at'
            )
            ->groupBy(
                'date'
            )
            ->orderBy(
                'date',
                'desc'
            )
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $topMedicines =
            MedicineLog::select(
                'medicine_id',
                DB::raw(
                    'SUM(quantity) as total'
                )
            )
            ->groupBy(
                'medicine_id'
            )
            ->orderByDesc(
                'total'
            )
            ->limit(7)
            ->with(
                'medicine:id,name'
            )
            ->get();

        $allRecords =
            \App\Models\MedicalRecord::all();

        $overallBmiStats = [
            'underweight' =>
                $allRecords
                    ->where(
                        'bmi_category',
                        'Underweight'
                    )
                    ->count(),

            'normal' =>
                $allRecords
                    ->where(
                        'bmi_category',
                        'Normal'
                    )
                    ->count(),

            'overweight' =>
                $allRecords
                    ->where(
                        'bmi_category',
                        'Overweight'
                    )
                    ->count(),

            'obese' =>
                $allRecords
                    ->where(
                        'bmi_category',
                        'Obese'
                    )
                    ->count(),
        ];

        return Inertia::render(
            'Reports/Index',
            [
                'departments' =>
                    Department::all(),

                'medicines' =>
                    Medicine::select(
                        'id',
                        'name',
                        'supplier'
                    )
                    ->orderBy(
                        'name'
                    )
                    ->get(),

                'topDiagnoses' =>
                    $topDiagnoses,

                'treatmentTrends' =>
                    $treatmentTrends,

                'topMedicines' =>
                    $topMedicines,

                'overallBmiStats' =>
                    $overallBmiStats,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Treatment Summary
    |--------------------------------------------------------------------------
    */

    public function treatmentSummary(
        Request $request
    ) {
        $request->validate([
            'date_from' =>
                'required|date',

            'date_to' =>
                'required|date|after_or_equal:date_from',

            'department_id' =>
                'nullable|exists:departments,id',
        ]);

        $treatments =
            Treatment::with(
                'treatedBy'
            )
            ->whereDate(
                'treated_at',
                '>=',
                $request->date_from
            )
            ->whereDate(
                'treated_at',
                '<=',
                $request->date_to
            )
            ->latest(
                'treated_at'
            )
            ->get()
            ->map(
                function ($treatment) {

                    $patient =
                        $treatment->patient;

                    $treatment->patient_name =
                        $patient
                            ? $patient->name
                            : 'Unknown';

                    $treatment->patient_type_label =
                        str_contains(
                            $treatment->patient_type,
                            'Student'
                        )
                            ? 'Student'
                            : 'Personnel';

                    return $treatment;
                }
            );

        $topDiagnoses =
            Treatment::whereDate(
                'treated_at',
                '>=',
                $request->date_from
            )
            ->whereDate(
                'treated_at',
                '<=',
                $request->date_to
            )
            ->select(
                'diagnosis',
                DB::raw(
                    'count(*) as total'
                )
            )
            ->groupBy(
                'diagnosis'
            )
            ->orderByDesc(
                'total'
            )
            ->limit(10)
            ->get();

        $pdf =
            Pdf::loadView(
                'reports.treatment-summary',
                [
                    'department' =>
                        $request->department_id
                            ? Department::find(
                                $request->department_id
                            )
                            : null,

                    'treatments' =>
                        $treatments,

                    'topDiagnoses' =>
                        $topDiagnoses,

                    'dateFrom' =>
                        $request->date_from,

                    'dateTo' =>
                        $request->date_to,
                ]
            );

        return $pdf->download(
            'treatment-summary-'
            . $request->date_from
            . '-to-'
            . $request->date_to
            . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Medicine Stock Card PDF
    |--------------------------------------------------------------------------
    |
    | ONLY the selected medicine is generated.
    |--------------------------------------------------------------------------
    */

    public function medicineUsage(
        Request $request,
        MedicineStockCardService $stockCardService
    ) {
        $validated =
            $request->validate([
                'medicine_id' =>
                    'required|exists:medicines,id',

                'date_from' =>
                    'required|date',

                'date_to' =>
                    'required|date|after_or_equal:date_from',
            ]);

        $medicine =
            Medicine::findOrFail(
                $validated[
                    'medicine_id'
                ]
            );

        $stockCard =
            $stockCardService->build(
                $medicine,
                $validated[
                    'date_from'
                ],
                $validated[
                    'date_to'
                ]
            );

        $pdf =
            Pdf::loadView(
                'reports.medicine-stock-card',
                [
                    'medicine' =>
                        $medicine,

                    'transactions' =>
                        $stockCard[
                            'transactions'
                        ],

                    'openingBalance' =>
                        $stockCard[
                            'openingBalance'
                        ],

                    'currentStock' =>
                        $stockCard[
                            'currentStock'
                        ],

                    'dateFrom' =>
                        $validated[
                            'date_from'
                        ],

                    'dateTo' =>
                        $validated[
                            'date_to'
                        ],
                ]
            )
            ->setPaper(
                'a4',
                'portrait'
            );

        return $pdf->download(
            'stock-card-'
            . Str::slug(
                $medicine->name
            )
            . '-'
            . $validated[
                'date_from'
            ]
            . '-to-'
            . $validated[
                'date_to'
            ]
            . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Department Report
    |--------------------------------------------------------------------------
    */

    public function departmentReport(
        Request $request
    ) {
        $request->validate([
            'department_id' =>
                'required|exists:departments,id',
        ]);

        $department =
            Department::with(
                'courses'
            )
            ->findOrFail(
                $request->department_id
            );

        $students =
            Student::where(
                'department_id',
                $department->id
            )
            ->with(
                'medicalRecord'
            )
            ->get();

        $personnel =
            Personnel::where(
                'department_id',
                $department->id
            )
            ->with(
                'medicalRecord'
            )
            ->get();

        $bmiStats = [

            'underweight' =>
                $students->filter(
                    fn ($student) =>
                        $student->medicalRecord
                        &&
                        $student
                            ->medicalRecord
                            ->bmi_category
                        ===
                        'Underweight'
                )->count(),

            'normal' =>
                $students->filter(
                    fn ($student) =>
                        $student->medicalRecord
                        &&
                        $student
                            ->medicalRecord
                            ->bmi_category
                        ===
                        'Normal'
                )->count(),

            'overweight' =>
                $students->filter(
                    fn ($student) =>
                        $student->medicalRecord
                        &&
                        $student
                            ->medicalRecord
                            ->bmi_category
                        ===
                        'Overweight'
                )->count(),

            'obese' =>
                $students->filter(
                    fn ($student) =>
                        $student->medicalRecord
                        &&
                        $student
                            ->medicalRecord
                            ->bmi_category
                        ===
                        'Obese'
                )->count(),
        ];

        $pdf =
            Pdf::loadView(
                'reports.department',
                [
                    'department' =>
                        $department,

                    'students' =>
                        $students,

                    'personnel' =>
                        $personnel,

                    'bmiStats' =>
                        $bmiStats,
                ]
            );

        return $pdf->download(
            'department-report-'
            . $department->code
            . '.pdf'
        );
    }
}
