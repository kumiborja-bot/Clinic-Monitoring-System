<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineStockMovement;
use App\Models\Personnel;
use App\Models\Student;
use App\Services\MedicineStockCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MedicineController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Medicine Inventory
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $medicines =
            Medicine::query()

            ->when(
                $request->search,
                function ($query, $search) {

                    $query->where(
                        function ($q) use ($search) {

                            $q->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'supplier',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
                }
            )

            ->when(
                $request->supplier,
                fn ($query, $supplier) =>
                    $query->where(
                        'supplier',
                        $supplier
                    )
            )

            ->latest()
            ->paginate(15)
            ->withQueryString();

        $suppliers =
            Medicine::whereNotNull(
                'supplier'
            )
            ->where(
                'supplier',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy(
                'supplier'
            )
            ->pluck(
                'supplier'
            );

        $topDispensed =
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
            ->limit(8)
            ->with(
                'medicine:id,name'
            )
            ->get();

        return Inertia::render(
            'Medicines/Index',
            [
                'medicines' =>
                    $medicines,

                'suppliers' =>
                    $suppliers,

                'topDispensed' =>
                    $topDispensed,

                'filters' =>
                    $request->only([
                        'search',
                        'supplier',
                    ]),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Add Medicine Page
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Medicines/Create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Medicine
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        $validated =
            $request->validate([
                'name' =>
                    'required|string|max:255',

                'description' =>
                    'nullable|string',

                'supplier' =>
                    'required|string|max:255',

                'unit' =>
                    'required|string|max:50',

                'stock_quantity' =>
                    'required|integer|min:0',

                'reorder_level' =>
                    'nullable|integer|min:0',

                'expiry_date' =>
                    'nullable|date',

                'initial_stock_date' =>
                    'required|date|before_or_equal:today',
            ]);

        DB::transaction(
            function () use ($validated) {

                $medicine =
                    Medicine::create([
                        'name' =>
                            $validated['name'],

                        'description' =>
                            $validated['description']
                            ?? null,

                        'supplier' =>
                            $validated['supplier'],

                        'unit' =>
                            $validated['unit'],

                        'stock_quantity' =>
                            $validated['stock_quantity'],

                        'reorder_level' =>
                            $validated['reorder_level']
                            ?? 10,

                        'expiry_date' =>
                            $validated['expiry_date']
                            ?? null,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Initial Stock = First IN Transaction
                |--------------------------------------------------------------------------
                */

                if (
                    $medicine->stock_quantity > 0
                ) {

                    MedicineStockMovement::create([
                        'medicine_id' =>
                            $medicine->id,

                        'transaction_date' =>
                            $validated[
                                'initial_stock_date'
                            ],

                        'type' =>
                            'in',

                        'quantity' =>
                            $medicine->stock_quantity,

                        'supplier' =>
                            $medicine->supplier,

                        'requester' =>
                            null,

                        'notes' =>
                            'Initial medicine stock.',
                    ]);
                }
            }
        );

        return redirect()
            ->route(
                'medicines.index'
            )
            ->with(
                'success',
                'Medicine added successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Medicine
    |--------------------------------------------------------------------------
    */

    public function edit(
        Medicine $medicine
    ) {
        return Inertia::render(
            'Medicines/Edit',
            [
                'medicine' =>
                    $medicine,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Medicine Information
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | Editing Medicine DOES NOT modify stock.
    |
    | Stock increases only through Stock In.
    | Stock decreases only through Dispense.
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Medicine $medicine
    ) {
        $validated =
            $request->validate([
                'name' =>
                    'required|string|max:255',

                'description' =>
                    'nullable|string',

                'supplier' =>
                    'required|string|max:255',

                'unit' =>
                    'required|string|max:50',

                'reorder_level' =>
                    'nullable|integer|min:0',

                'expiry_date' =>
                    'nullable|date',
            ]);

        $medicine->update(
            $validated
        );

        return redirect()
            ->route(
                'medicines.index'
            )
            ->with(
                'success',
                'Medicine updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Stock In Page
    |--------------------------------------------------------------------------
    */

    public function stockInForm(
        Request $request
    ) {
        return Inertia::render(
            'Medicines/StockIn',
            [
                'medicines' =>
                    Medicine::select(
                        'id',
                        'name',
                        'supplier',
                        'stock_quantity',
                        'unit'
                    )
                    ->orderBy(
                        'name'
                    )
                    ->get(),

                'selectedMedicineId' =>
                    $request->medicine_id,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Stock In
    |--------------------------------------------------------------------------
    */

    public function stockIn(
        Request $request
    ) {
        $validated =
            $request->validate([
                'medicine_id' =>
                    'required|exists:medicines,id',

                'supplier' =>
                    'required|string|max:255',

                'quantity' =>
                    'required|integer|min:1',

                'transaction_date' =>
                    'required|date|before_or_equal:today',

                'notes' =>
                    'nullable|string|max:1000',
            ]);

        DB::transaction(
            function () use ($validated) {

                $medicine =
                    Medicine::whereKey(
                        $validated[
                            'medicine_id'
                        ]
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Add IN Movement
                |--------------------------------------------------------------------------
                */

                MedicineStockMovement::create([
                    'medicine_id' =>
                        $medicine->id,

                    'transaction_date' =>
                        $validated[
                            'transaction_date'
                        ],

                    'type' =>
                        'in',

                    'quantity' =>
                        $validated[
                            'quantity'
                        ],

                    'supplier' =>
                        $validated[
                            'supplier'
                        ],

                    'requester' =>
                        null,

                    'notes' =>
                        $validated[
                            'notes'
                        ] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Increase Current Stock
                |--------------------------------------------------------------------------
                */

                $medicine->increment(
                    'stock_quantity',
                    $validated[
                        'quantity'
                    ]
                );
            }
        );

        return redirect()
            ->route(
                'medicines.index'
            )
            ->with(
                'success',
                'Medicine stock added successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Individual Stock Card
    |--------------------------------------------------------------------------
    */

    public function stockCard(
        Medicine $medicine,
        MedicineStockCardService $stockCardService
    ) {
        $stockCard =
            $stockCardService->build(
                $medicine
            );

        return Inertia::render(
            'Medicines/StockCard',
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
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dispense Page
    |--------------------------------------------------------------------------
    */

    public function dispenseForm()
    {
        return Inertia::render(
            'Medicines/Dispense',
            [
                'medicines' =>
                    Medicine::where(
                        'stock_quantity',
                        '>',
                        0
                    )
                    ->orderBy(
                        'name'
                    )
                    ->get(),

                'students' =>
                    Student::select(
                        'id',
                        'name',
                        'student_id_number',
                        'department_id'
                    )
                    ->with(
                        'department:id,name,code'
                    )
                    ->get(),

                'personnel' =>
                    Personnel::select(
                        'id',
                        'name',
                        'employee_id',
                        'department_id'
                    )
                    ->with(
                        'department:id,name,code'
                    )
                    ->get(),

                'departments' =>
                    Department::select(
                        'id',
                        'name',
                        'code',
                        'type'
                    )
                    ->get(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dispense Medicine
    |--------------------------------------------------------------------------
    */

    public function dispense(
        Request $request
    ) {
        $validated =
            $request->validate([
                'medicine_id' =>
                    'required|exists:medicines,id',

                'patient_type' =>
                    'required|in:student,personnel',

                'patient_id' =>
                    'required|integer',

                'quantity' =>
                    'required|integer|min:1',

                'notes' =>
                    'nullable|string',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Resolve Patient
        |--------------------------------------------------------------------------
        */

        if (
            $validated[
                'patient_type'
            ] === 'student'
        ) {

            $patient =
                Student::findOrFail(
                    $validated[
                        'patient_id'
                    ]
                );

            $patientClass =
                Student::class;

        } else {

            $patient =
                Personnel::findOrFail(
                    $validated[
                        'patient_id'
                    ]
                );

            $patientClass =
                Personnel::class;
        }

        DB::transaction(
            function () use (
                $validated,
                $patient,
                $patientClass
            ) {

                $medicine =
                    Medicine::whereKey(
                        $validated[
                            'medicine_id'
                        ]
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($medicine->stock_quantity < $validated['quantity']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => 'Insufficient stock. Only '.$medicine->stock_quantity.' available.',
                    ]);
                }

                MedicineLog::create([
                    'medicine_id' => $medicine->id,
                    'patient_type' => $patientClass,
                    'patient_id' => $patient->id,
                    'quantity' => $validated['quantity'],
                    'dispensed_by' => auth()->id(),
                    'dispensed_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                $medicine->decrement(
                    'stock_quantity',
                    $validated[
                        'quantity'
                    ]
                );
            }
        );

        return redirect()
            ->route(
                'medicines.index'
            )
            ->with(
                'success',
                'Medicine dispensed successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Medicine Log Book
    |--------------------------------------------------------------------------
    */

    public function logs(
        Request $request
    ) {
        $logs =
            MedicineLog::with([
                'medicine:id,name,supplier',
                'dispenser:id,name',
            ])

            ->when(
                $request->medicine_id,
                fn ($query, $id) =>
                    $query->where(
                        'medicine_id',
                        $id
                    )
            )

            ->when(
                $request->patient_type,
                function (
                    $query,
                    $type
                ) {

                    $class =
                        $type === 'student'
                            ? Student::class
                            : Personnel::class;

                    $query->where(
                        'patient_type',
                        $class
                    );
                }
            )

            ->when(
                $request->date_from,
                fn ($query, $date) =>
                    $query->whereDate(
                        'dispensed_at',
                        '>=',
                        $date
                    )
            )

            ->when(
                $request->date_to,
                fn ($query, $date) =>
                    $query->whereDate(
                        'dispensed_at',
                        '<=',
                        $date
                    )
            )

            ->latest(
                'dispensed_at'
            )

            ->paginate(20)
            ->withQueryString()

            ->through(
                function ($log) {

                    $patient =
                        $log->patient;

                    $log->patient_name =
                        $patient
                            ? $patient->name
                            : 'Unknown';

                    $log->patient_type_label =
                        str_contains(
                            $log->patient_type,
                            'Student'
                        )
                            ? 'Student'
                            : 'Personnel';

                    return $log;
                }
            );

        return Inertia::render(
            'Medicines/Logs',
            [
                'logs' =>
                    $logs,

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

                'filters' =>
                    $request->only([
                        'medicine_id',
                        'patient_type',
                        'date_from',
                        'date_to',
                    ]),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Medicine
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Medicine $medicine
    ) {
        DB::transaction(
            function () use (
                $medicine
            ) {

                MedicineLog::where(
                    'medicine_id',
                    $medicine->id
                )->delete();

                $medicine->delete();
            }
        );

        return redirect()
            ->route(
                'medicines.index'
            )
            ->with(
                'success',
                'Medicine deleted.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(
        Request $request
    ) {
        $validated =
            $request->validate([
                'ids' =>
                    'required|array|min:1',

                'ids.*' =>
                    'integer|exists:medicines,id',
            ]);

        DB::transaction(
            function () use (
                $validated
            ) {

                MedicineLog::whereIn(
                    'medicine_id',
                    $validated['ids']
                )->delete();

                Medicine::whereIn(
                    'id',
                    $validated['ids']
                )->delete();
            }
        );

        return redirect()
            ->route(
                'medicines.index'
            )
            ->with(
                'success',
                count(
                    $validated['ids']
                )
                . ' medicine(s) deleted.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete All
    |--------------------------------------------------------------------------
    */

    public function deleteAll()
    {
        $count =
            Medicine::count();

        DB::transaction(
            function () {

                MedicineLog::query()
                    ->delete();

                Medicine::query()
                    ->delete();
            }
        );

        return redirect()
            ->route(
                'medicines.index'
            )
            ->with(
                'success',
                $count
                . ' medicine(s) deleted.'
            );
    }
}
