<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\Student;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $medicines = Medicine::when($request->search, function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        })
            ->when($request->category, fn ($q, $cat) => $q->where('category', $cat))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Medicine::whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $topDispensed = MedicineLog::select('medicine_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('medicine_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('medicine:id,name')
            ->get();

        return Inertia::render('Medicines/Index', [
            'medicines' => $medicines,
            'categories' => $categories,
            'topDispensed' => $topDispensed,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Medicines/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        Medicine::create($validated);
        return redirect()->route('medicines.index')->with('success', 'Medicine added.');
    }

    public function edit(Medicine $medicine)
    {
        return Inertia::render('Medicines/Edit', ['medicine' => $medicine]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $medicine->update($validated);
        return redirect()->route('medicines.index')->with('success', 'Medicine updated.');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return redirect()->route('medicines.index')->with('success', 'Medicine deleted.');
    }

    public function dispenseForm()
    {
        return Inertia::render('Medicines/Dispense', [
            'medicines' => Medicine::where('stock_quantity', '>', 0)->get(),
            'students' => Student::select('id', 'name', 'student_id_number', 'department_id')
            ->with('department:id,name,code')->get(),
            'personnel' => Personnel::select('id', 'name', 'employee_id', 'department_id')
            ->with('department:id,name,code')->get(),
            'departments' => Department::select('id', 'name', 'code', 'type')->get(),
        ]);
    }

    public function dispense(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'patient_type' => 'required|in:student,personnel',
            'patient_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $medicine = Medicine::findOrFail($request->medicine_id);

        if ($medicine->stock_quantity < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Only ' . $medicine->stock_quantity . ' available.']);
        }

        $patientType = $request->patient_type === 'student'
            ? 'App\\Models\\Student'
            : 'App\\Models\\Personnel';

        MedicineLog::create([
            'medicine_id' => $request->medicine_id,
            'patient_type' => $patientType,
            'patient_id' => $request->patient_id,
            'quantity' => $request->quantity,
            'dispensed_by' => auth()->id(),
            'dispensed_at' => now(),
            'notes' => $request->notes,
        ]);

        $medicine->decrement('stock_quantity', $request->quantity);

        return redirect()->route('medicines.index')->with('success', 'Medicine dispensed.');
    }

    public function logs(Request $request)
    {
        $logs = MedicineLog::with(['medicine:id,name', 'dispenser:id,name'])
            ->when($request->medicine_id, fn ($q, $id) => $q->where('medicine_id', $id))
            ->when($request->patient_type, function ($q, $type) {
                $class = $type === 'student' ? 'App\\Models\\Student' : 'App\\Models\\Personnel';
                $q->where('patient_type', $class);
            })
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('dispensed_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('dispensed_at', '<=', $d))
            ->latest('dispensed_at')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($log) {
                $patient = $log->patient;
                $log->patient_name = $patient ? $patient->name : 'Unknown';
                $log->patient_type_label = str_contains($log->patient_type, 'Student') ? 'Student' : 'Personnel';
                return $log;
            });

        return Inertia::render('Medicines/Logs', [
            'logs' => $logs,
            'medicines' => Medicine::select('id', 'name')->get(),
            'filters' => $request->only(['medicine_id', 'patient_type', 'date_from', 'date_to']),
        ]);
    }
}
