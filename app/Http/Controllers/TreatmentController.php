<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\Student;
use App\Models\Personnel;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Medicine;
use App\Models\MedicineLog;

class TreatmentController extends Controller
{
    public function index(Request $request)
    {
        $treatments = Treatment::with(['treatedBy', 'medicine'])
            ->when($request->search, function ($q, $search) {
                $q->where('diagnosis', 'like', "%{$search}%");
            })
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('treated_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('treated_at', '<=', $d))
            ->latest('treated_at')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($t) {
                $patient = $t->patient;
                $t->patient_name = $patient ? $patient->name : 'Unknown';
                $t->patient_type_label = str_contains($t->patient_type, 'Student') ? 'Student' : 'Personnel';
                $t->treated_by_user = $t->treatedBy;
                return $t;
            });

        return Inertia::render('Treatments/Index', [
            'treatments' => $treatments,
            'departments' => Department::all(),
            'filters' => $request->only(['search', 'department_id', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Treatments/Create', [
            'students' => Student::select('id', 'name', 'student_id_number', 'department_id')
                ->with('department:id,name,code')->get(),
            'personnel' => Personnel::select('id', 'name', 'employee_id', 'department_id')
                ->with('department:id,name,code')->get(),
            'departments' => Department::select('id', 'name', 'code', 'type')->get(),
            'medicines' => Medicine::select('id', 'name', 'stock_quantity', 'unit', 'category', 'description')
                ->where('stock_quantity', '>', 0)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_type' => 'required|in:student,personnel',
            'patient_id' => 'required|integer',
            'diagnosis' => 'required|string|max:255',
            'treatment_given' => 'nullable|string',
            'medication_given' => 'nullable|string',
            'medicine_id' => 'nullable|exists:medicines,id',
            'medicine_quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'required|in:completed,follow-up',
            'follow_up_date' => 'nullable|date',
        ]);

        $patientType = $request->patient_type === 'student'
            ? 'App\\Models\\Student'
            : 'App\\Models\\Personnel';

        $medicationText = $request->medication_given;
        $medicine = null;
        $qty = $request->medicine_quantity ?? 1;

        if ($request->medicine_id) {
            $medicine = Medicine::find($request->medicine_id);
            if ($medicine) {
                if (empty($medicationText)) {
                    $medicationText = "{$medicine->name} (Qty: {$qty} {$medicine->unit})";
                } else {
                    $medicationText .= " - {$medicine->name} (Qty: {$qty} {$medicine->unit})";
                }
            }
        }

        $treatment = Treatment::create([
            'patient_type' => $patientType,
            'patient_id' => $request->patient_id,
            'diagnosis' => $request->diagnosis,
            'treatment_given' => $request->treatment_given,
            'medication_given' => $medicationText,
            'medicine_id' => $medicine?->id,
            'medicine_quantity' => $medicine ? $qty : null,
            'notes' => $request->notes,
            'status' => $request->status,
            'follow_up_date' => $request->follow_up_date,
            'treated_by' => auth()->id(),
            'treated_at' => now(),
        ]);

        if ($medicine) {
            $medicine->decrement('stock_quantity', $qty);

            MedicineLog::create([
                'medicine_id' => $medicine->id,
                'treatment_id' => $treatment->id,
                'patient_type' => $patientType,
                'patient_id' => $request->patient_id,
                'quantity' => $qty,
                'dispensed_by' => auth()->id(),
                'dispensed_at' => now(),
                'notes' => 'Dispensed during treatment recording',
            ]);
        }

        return redirect()->route('treatments.index')->with('success', 'Treatment recorded and medicine linked.');
    }

    public function show(Treatment $treatment)
    {
        $treatment->load('treatedBy');
        $patient = $treatment->patient;
        $treatment->patient_name = $patient ? $patient->name : 'Unknown';
        $treatment->patient_type_label = str_contains($treatment->patient_type, 'Student') ? 'Student' : 'Personnel';
        $treatment->treated_by_user = $treatment->treatedBy;

        return Inertia::render('Treatments/Show', [
            'treatment' => $treatment,
        ]);
    }

    public function destroy(Treatment $treatment)
    {
        $treatment->delete();
        return redirect()->route('treatments.index')->with('success', 'Treatment deleted.');
    }
}
