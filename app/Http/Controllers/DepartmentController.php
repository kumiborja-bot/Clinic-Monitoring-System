<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::when($request->search, function ($q, $search) {
            $q->where(function ($inner) use ($search) {
                $inner->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        })
        ->when($request->type, fn ($q, $type) => $q->where('type', $type))
        ->orderBy('code')
        ->paginate(15)
        ->withQueryString();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Departments/Create', [
            'default_type' => $request->type ?? 'student'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:departments,code',
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'type' => 'required|in:student,personnel',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        Department::create($validated);

        return redirect()->route('departments.index', ['type' => $validated['type']])->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return Inertia::render('Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'type' => 'required|in:student,personnel',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $department->update($validated);

        return redirect()->route('departments.index', ['type' => $validated['type']])->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // Prevent deletion if there are associated students or personnel
        if ($department->students()->exists() || $department->personnel()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete department with associated students or personnel.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
