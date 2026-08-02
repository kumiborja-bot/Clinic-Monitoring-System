<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PersonnelDocumentController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\MaritimeDocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OfflineQueueController;
use App\Http\Controllers\CsvDataController;
use App\Http\Controllers\DssRecordController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PrescriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

    // Students
    Route::post('/students/import-csv', [StudentController::class, 'importCsv'])->name('students.import-csv');
    Route::post('/students/import-local', [StudentController::class, 'importLocal'])->name('students.import-local');
    Route::resource('students', StudentController::class);
    Route::get('/api/departments/{department}/courses', [StudentController::class , 'getCourses'])->name('departments.courses');

    // Personnel
    Route::post('/personnel/import-csv', [PersonnelController::class, 'importCsv'])->name('personnel.import-csv');
    Route::resource('personnel', PersonnelController::class);

    // Departments
    Route::resource('departments', DepartmentController::class);

    // Master Data Hub
    Route::get('/master-data', function () {
        return Inertia::render('MasterData/Index');
    })->name('master-data.index');

    // Medicines
    Route::get('/medicines/dispense', [MedicineController::class , 'dispenseForm'])->name('medicines.dispense.form');
    Route::post('/medicines/dispense', [MedicineController::class , 'dispense'])->name('medicines.dispense');
    Route::get('/medicines/logs', [MedicineController::class , 'logs'])->name('medicines.logs');
    Route::resource('medicines', MedicineController::class)->except(['show']);

    // Treatments
    Route::resource('treatments', TreatmentController::class)->except(['edit', 'update']);

    // Maritime Documents
    Route::post('/students/{student}/documents', [MaritimeDocumentController::class , 'store'])->name('maritime.store');
    Route::get('/maritime-documents/{document}/download', [MaritimeDocumentController::class , 'download'])->name('maritime.download');
    Route::delete('/maritime-documents/{document}', [MaritimeDocumentController::class , 'destroy'])->name('maritime.destroy');

    // Personnel Documents & Weight Logs
    Route::post('/personnel/{personnel}/documents', [PersonnelDocumentController::class, 'store'])->name('personnel-documents.store');
    Route::get('/personnel-documents/{document}/download', [PersonnelDocumentController::class, 'download'])->name('personnel-documents.download');
    Route::delete('/personnel-documents/{document}', [PersonnelDocumentController::class, 'destroy'])->name('personnel-documents.destroy');
    Route::post('/personnel/{personnel}/weight-logs', [PersonnelController::class, 'storeWeightLog'])->name('personnel.weight-logs.store');
    Route::delete('/personnel-weight-logs/{log}', [PersonnelController::class, 'destroyWeightLog'])->name('personnel.weight-logs.destroy');

    // Reports
    Route::get('/reports', [ReportController::class , 'index'])->name('reports.index');
    Route::get('/reports/treatment-summary', [ReportController::class , 'treatmentSummary'])->name('reports.treatment-summary');
    Route::get('/reports/medicine-usage', [ReportController::class , 'medicineUsage'])->name('reports.medicine-usage');
    Route::get('/reports/department', [ReportController::class , 'departmentReport'])->name('reports.department');

    // Profile
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    // Offline Queue Sync
    Route::post('/api/offline-queue/sync', [OfflineQueueController::class , 'sync'])->name('offline-queue.sync');

    // CSV Data endpoints
    Route::get('/api/csv-patients', [CsvDataController::class, 'searchPatient'])->name('csv.patients.search');
    Route::get('/api/csv-dss', [CsvDataController::class, 'getDss'])->name('csv.dss');

    // Appointments
    Route::post('/appointments/{appointment}/send-reminder', [AppointmentController::class, 'sendReminder'])->name('appointments.send-reminder');
    Route::post('/appointments/{appointment}/record-consultation', [AppointmentController::class, 'recordConsultation'])->name('appointments.record-consultation');
    Route::resource('appointments', AppointmentController::class);

    // Prescriptions
    Route::resource('prescriptions', PrescriptionController::class)->only(['store', 'destroy']);

    // DSS Master Data
    Route::post('/dss/import-csv', [DssRecordController::class, 'importCsv'])->name('dss.import-csv');
    Route::resource('dss', DssRecordController::class)->except(['show', 'create', 'edit']);
});

require __DIR__ . '/auth.php';
