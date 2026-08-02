<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\MedicineLog;
use App\Models\Department;
use App\Models\Student;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $topDiagnoses = Treatment::select('diagnosis', DB::raw('count(*) as total'))
            ->whereNotNull('diagnosis')
            ->groupBy('diagnosis')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        $treatmentTrends = Treatment::select(DB::raw('DATE(treated_at) as date'), DB::raw('count(*) as total'))
            ->whereNotNull('treated_at')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $topMedicines = MedicineLog::select('medicine_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('medicine_id')
            ->orderByDesc('total')
            ->limit(7)
            ->with('medicine:id,name')
            ->get();

        $allRecords = \App\Models\MedicalRecord::all();
        $overallBmiStats = [
            'underweight' => $allRecords->where('bmi_category', 'Underweight')->count(),
            'normal' => $allRecords->where('bmi_category', 'Normal')->count(),
            'overweight' => $allRecords->where('bmi_category', 'Overweight')->count(),
            'obese' => $allRecords->where('bmi_category', 'Obese')->count(),
        ];

        return Inertia::render('Reports/Index', [
            'departments' => Department::all(),
            'topDiagnoses' => $topDiagnoses,
            'treatmentTrends' => $treatmentTrends,
            'topMedicines' => $topMedicines,
            'overallBmiStats' => $overallBmiStats,
        ]);
    }

    public function treatmentSummary(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $treatments = Treatment::with('treatedBy')
            ->whereDate('treated_at', '>=', $request->date_from)
            ->whereDate('treated_at', '<=', $request->date_to)
            ->latest('treated_at')
            ->get()
            ->map(function ($t) {
                $patient = $t->patient;
                $t->patient_name = $patient ? $patient->name : 'Unknown';
                $t->patient_type_label = str_contains($t->patient_type, 'Student') ? 'Student' : 'Personnel';
                return $t;
            });

        $topDiagnoses = Treatment::whereDate('treated_at', '>=', $request->date_from)
            ->whereDate('treated_at', '<=', $request->date_to)
            ->select('diagnosis', DB::raw('count(*) as total'))
            ->groupBy('diagnosis')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $pdf = Pdf::loadView('reports.treatment-summary', [
            'department' => $request->department_id ? Department::find($request->department_id) : null,
            'treatments' => $treatments,
            'topDiagnoses' => $topDiagnoses,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
        ]);

        return $pdf->download('treatment-summary-' . $request->date_from . '-to-' . $request->date_to . '.pdf');
    }

    public function medicineUsage(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $logs = MedicineLog::with(['medicine', 'dispenser'])
            ->whereDate('dispensed_at', '>=', $request->date_from)
            ->whereDate('dispensed_at', '<=', $request->date_to)
            ->latest('dispensed_at')
            ->get()
            ->map(function ($log) {
                $patient = $log->patient;
                $log->patient_name = $patient ? $patient->name : 'Unknown';
                return $log;
            });

        $topMedicines = MedicineLog::whereDate('dispensed_at', '>=', $request->date_from)
            ->whereDate('dispensed_at', '<=', $request->date_to)
            ->select('medicine_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('medicine_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('medicine:id,name')
            ->get();

        $pdf = Pdf::loadView('reports.medicine-usage', [
            'logs' => $logs,
            'topMedicines' => $topMedicines,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
        ]);

        return $pdf->download('medicine-usage-' . $request->date_from . '-to-' . $request->date_to . '.pdf');
    }

    public function departmentReport(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
        ]);

        $department = Department::with('courses')->findOrFail($request->department_id);
        $students = Student::where('department_id', $department->id)
            ->with('medicalRecord')
            ->get();
        $personnel = Personnel::where('department_id', $department->id)
            ->with('medicalRecord')
            ->get();

        $bmiStats = [
            'underweight' => $students->filter(fn ($s) => $s->medicalRecord && $s->medicalRecord->bmi_category === 'Underweight')->count(),
            'normal' => $students->filter(fn ($s) => $s->medicalRecord && $s->medicalRecord->bmi_category === 'Normal')->count(),
            'overweight' => $students->filter(fn ($s) => $s->medicalRecord && $s->medicalRecord->bmi_category === 'Overweight')->count(),
            'obese' => $students->filter(fn ($s) => $s->medicalRecord && $s->medicalRecord->bmi_category === 'Obese')->count(),
        ];

        $pdf = Pdf::loadView('reports.department', [
            'department' => $department,
            'students' => $students,
            'personnel' => $personnel,
            'bmiStats' => $bmiStats,
        ]);

        return $pdf->download('department-report-' . $department->code . '.pdf');
    }
}
