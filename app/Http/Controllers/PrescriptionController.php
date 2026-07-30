<?php

namespace App\Http\Controllers;

use App\Mail\PrescriptionMail;
use App\Models\Appointment;
use App\Models\Personnel;
use App\Models\Prescription;
use App\Models\Student;
use App\Models\Treatment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PrescriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_type' => 'required|string',
            'patient_id' => 'required|integer',
            'prescriber_name' => 'nullable|string|max:255',
            'medication_details' => 'required|array|min:1',
            'medication_details.*.medicine_name' => 'required|string|max:255',
            'medication_details.*.dosage' => 'nullable|string|max:255',
            'medication_details.*.frequency' => 'nullable|string|max:255',
            'medication_details.*.duration' => 'nullable|string|max:255',
            'medication_details.*.instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'send_email' => 'boolean',
        ]);

        $patientType = in_array($validated['patient_type'], ['student', Student::class]) ? Student::class : Personnel::class;

        $prescribedBy = auth()->id();
        $prescriberName = $validated['prescriber_name'] ?? auth()->user()?->name ?? 'Healthcare Provider';

        $prescription = Prescription::create([
            'appointment_id' => $validated['appointment_id'] ?? null,
            'patient_type' => $patientType,
            'patient_id' => $validated['patient_id'],
            'prescribed_by' => $prescribedBy,
            'prescriber_name' => $prescriberName,
            'medication_details' => $validated['medication_details'],
            'notes' => $validated['notes'] ?? null,
            'issued_at' => now(),
            'status' => 'issued',
        ]);

        // System updates patient health record (add Treatment record summary for prescription)
        $medSummary = collect($validated['medication_details'])
            ->map(fn ($m) => ($m['medicine_name'] ?? '') . ' (' . ($m['dosage'] ?? '') . ')')
            ->implode(', ');

        Treatment::create([
            'patient_type' => $validated['patient_type'],
            'patient_id' => $validated['patient_id'],
            'diagnosis' => 'Prescription Issued',
            'treatment_given' => 'Medical Prescription #' . $prescription->id,
            'medication_given' => $medSummary,
            'treated_by' => $prescribedBy,
            'treated_at' => now(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'completed',
        ]);

        // If linked to an appointment, mark appointment as completed/attended
        if (!empty($validated['appointment_id'])) {
            $appointment = Appointment::find($validated['appointment_id']);
            if ($appointment && in_array($appointment->status, ['scheduled', 'pending', 'attended'])) {
                $appointment->update(['status' => 'completed']);
            }
        }

        // Send prescription via email if requested
        $patientClass = $validated['patient_type'];
        $patient = $patientClass::find($validated['patient_id']);
        $email = $appointment->email ?? $patient?->email;

        if ($request->boolean('send_email') && $email) {
            try {
                Mail::to($email)->send(new PrescriptionMail($prescription));
            } catch (\Throwable $e) {
                // Log exception silently
            }
        }

        return redirect()->back()->with('success', 'Prescription recorded and issued successfully.');
    }

    public function destroy(Prescription $prescription): RedirectResponse
    {
        $prescription->update(['status' => 'cancelled']);
        return redirect()->back()->with('success', 'Prescription cancelled successfully.');
    }
}
