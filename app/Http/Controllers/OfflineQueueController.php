<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineStockMovement;
use App\Models\Student;
use App\Models\Personnel;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OfflineQueueController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'type' => 'required|in:treatment,medicine_dispense,student,personnel,medicine,maritime_document',
            'data' => 'required|array',
        ]);

        $type = $request->type;
        $data = $request->data;

        try {
            DB::beginTransaction();

            switch ($type) {
                case 'treatment':
                    $result = $this->syncTreatment($data);
                    break;
                case 'medicine_dispense':
                    $result = $this->syncMedicineDispense($data);
                    break;
                case 'student':
                    $result = $this->syncStudent($data);
                    break;
                case 'personnel':
                    $result = $this->syncPersonnel($data);
                    break;
                case 'medicine':
                    $result = $this->syncMedicine($data);
                    break;
                case 'maritime_document':
                    $result = $this->syncMaritimeDocument($data);
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', collect($e->errors())->flatten()->toArray()),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function syncTreatment(array $data): array
    {
        $validator = Validator::make($data, [
            'patient_type' => 'required|in:student,personnel',
            'patient_id' => 'required|integer',
            'diagnosis' => 'required|string|max:255',
            'treatment_given' => 'nullable|string',
            'medication_given' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:completed,follow-up',
        ]);

        $validator->validate();

        $patientType = $data['patient_type'] === 'student'
            ? 'App\\Models\\Student'
            : 'App\\Models\\Personnel';

        Treatment::create([
            'patient_type' => $patientType,
            'patient_id' => $data['patient_id'],
            'diagnosis' => $data['diagnosis'],
            'treatment_given' => $data['treatment_given'] ?? null,
            'medication_given' => $data['medication_given'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'],
            'treated_by' => auth()->id(),
            'treated_at' => now(),
        ]);

        return ['message' => 'Treatment synced successfully.'];
    }

    private function syncMedicineDispense(array $data): array
    {
        $validator = Validator::make($data, [
            'medicine_id' => 'required|exists:medicines,id',
            'patient_type' => 'required|in:student,personnel',
            'patient_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validator->validate();

        $medicine = Medicine::findOrFail($data['medicine_id']);

        if ($medicine->stock_quantity < $data['quantity']) {
            throw new \Exception('Insufficient stock for ' . $medicine->name . '. Only ' . $medicine->stock_quantity . ' available.');
        }

        $patientType = $data['patient_type'] === 'student'
            ? 'App\\Models\\Student'
            : 'App\\Models\\Personnel';

        MedicineLog::create([
            'medicine_id' => $data['medicine_id'],
            'patient_type' => $patientType,
            'patient_id' => $data['patient_id'],
            'quantity' => $data['quantity'],
            'dispensed_by' => auth()->id(),
            'dispensed_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $medicine->decrement('stock_quantity', $data['quantity']);

        return ['message' => 'Medicine dispensed successfully.'];
    }

    private function syncStudent(array $data): array
    {
        $validator = Validator::make($data, [
            'student_id_number' => 'required|unique:students',
            'name' => 'required|string|max:255',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'age' => 'nullable|integer',
            'sex' => 'nullable|in:Male,Female',
            'civil_status' => 'nullable|string',
            'religion' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'course_id' => 'nullable|exists:courses,id',
            'contact_no' => 'nullable|string',
            'year_level' => 'nullable|string',
            'emergency_contact_person' => 'nullable|string',
            'emergency_contact_relationship' => 'nullable|string',
            'emergency_contact_address' => 'nullable|string',
            'emergency_contact_no' => 'nullable|string',
        ]);

        $validator->validate();

        $studentData = collect($data)->only([
            'student_id_number', 'name', 'birthdate', 'address', 'age', 'sex',
            'civil_status', 'religion', 'department_id', 'course_id', 'contact_no',
            'year_level', 'emergency_contact_person', 'emergency_contact_relationship',
            'emergency_contact_address', 'emergency_contact_no',
        ])->toArray();

        $student = Student::create($studentData);

        // Create medical record if medical data provided
        if (!empty($data['height_cm']) || !empty($data['weight_kg'])) {
            $medicalFields = [
                'examination_date', 'height_cm', 'weight_kg', 'pulse_rate',
                'respiratory_rate', 'blood_pressure', 'oxygen_saturation',
                'smoker', 'alcoholic', 'allergies', 'food_allergy', 'drug_allergy',
                'past_medical_history', 'family_hpn', 'family_cancer',
                'family_asthma', 'family_dm', 'physician_name',
                'physician_license_no', 'physician_ptr', 'license_expiry_date',
            ];

            $medicalData = collect($data)->only($medicalFields)->toArray();

            if (!empty($medicalData['height_cm']) && !empty($medicalData['weight_kg'])) {
                $bmiResult = MedicalRecord::calculateBmi($medicalData['height_cm'], $medicalData['weight_kg']);
                $medicalData['bmi'] = $bmiResult['bmi'];
                $medicalData['bmi_category'] = $bmiResult['category'];
            }

            $student->medicalRecord()->create($medicalData);
        }

        return ['message' => 'Student synced successfully.'];
    }

    private function syncPersonnel(array $data): array
    {
        $validator = Validator::make($data, [
            'employee_id' => 'required|unique:personnel',
            'name' => 'required|string|max:255',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'age' => 'nullable|integer',
            'sex' => 'nullable|in:Male,Female',
            'civil_status' => 'nullable|string',
            'religion' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'position' => 'nullable|string',
            'contact_no' => 'nullable|string',
            'emergency_contact_person' => 'nullable|string',
            'emergency_contact_relationship' => 'nullable|string',
            'emergency_contact_address' => 'nullable|string',
            'emergency_contact_no' => 'nullable|string',
        ]);

        $validator->validate();

        $personnelData = collect($data)->only([
            'employee_id', 'name', 'birthdate', 'address', 'age', 'sex',
            'civil_status', 'religion', 'department_id', 'position', 'contact_no',
            'emergency_contact_person', 'emergency_contact_relationship',
            'emergency_contact_address', 'emergency_contact_no',
        ])->toArray();

        $person = Personnel::create($personnelData);

        // Create medical record if medical data provided
        if (!empty($data['height_cm']) || !empty($data['weight_kg'])) {
            $medicalFields = [
                'examination_date', 'height_cm', 'weight_kg', 'pulse_rate',
                'respiratory_rate', 'blood_pressure', 'oxygen_saturation',
                'smoker', 'alcoholic', 'allergies', 'food_allergy', 'drug_allergy',
                'past_medical_history', 'family_hpn', 'family_cancer',
                'family_asthma', 'family_dm', 'physician_name',
                'physician_license_no', 'physician_ptr', 'license_expiry_date',
            ];

            $medicalData = collect($data)->only($medicalFields)->toArray();

            if (!empty($medicalData['height_cm']) && !empty($medicalData['weight_kg'])) {
                $bmiResult = MedicalRecord::calculateBmi($medicalData['height_cm'], $medicalData['weight_kg']);
                $medicalData['bmi'] = $bmiResult['bmi'];
                $medicalData['bmi_category'] = $bmiResult['category'];
            }

            $person->medicalRecord()->create($medicalData);
        }

        return ['message' => 'Personnel synced successfully.'];
    }

    private function syncMedicine(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'supplier' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'stock_quantity' => 'required|integer|min:0',
            'initial_stock_date' => 'required|date|before_or_equal:today',
            'reorder_level' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $validator->validate();

        $medicine = Medicine::create(collect($data)->only([
            'name', 'description', 'supplier', 'unit',
            'stock_quantity', 'reorder_level', 'expiry_date',
        ])->toArray());

        if ($medicine->stock_quantity > 0) {
            MedicineStockMovement::create([
                'medicine_id' => $medicine->id,
                'transaction_date' => $data['initial_stock_date'],
                'type' => 'in',
                'quantity' => $medicine->stock_quantity,
                'supplier' => $medicine->supplier,
                'notes' => 'Initial medicine stock.',
            ]);
        }

        return ['message' => 'Medicine synced successfully.'];
    }

    private function syncMaritimeDocument(array $data): array
    {
        $validator = Validator::make($data, [
            'student_id' => 'required|exists:students,id',
            'file_base64' => 'required|string',
            'file_name' => 'required|string|max:255',
            'file_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $validator->validate();

        // Decode base64 file data
        $base64Data = $data['file_base64'];
        if (str_contains($base64Data, ',')) {
            $base64Data = explode(',', $base64Data, 2)[1];
        }
        $fileContent = base64_decode($base64Data);

        // Store file
        $fileName = time() . '_' . $data['file_name'];
        $path = 'maritime-documents/' . $fileName;
        Storage::disk('public')->put($path, $fileContent);

        $student = Student::findOrFail($data['student_id']);
        $student->maritimeDocuments()->create([
            'file_name' => $data['file_name'],
            'file_path' => $path,
            'file_type' => $data['file_type'],
            'file_size' => strlen($fileContent),
            'description' => $data['description'] ?? null,
        ]);

        return ['message' => 'Maritime document synced successfully.'];
    }
}
