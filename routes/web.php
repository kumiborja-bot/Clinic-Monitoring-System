<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\MaritimeDocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OfflineQueueController;
use App\Http\Controllers\CsvDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

    // Students
    Route::resource('students', StudentController::class);
    Route::get('/api/departments/{department}/courses', [StudentController::class , 'getCourses'])->name('departments.courses');

    // Personnel
    Route::resource('personnel', PersonnelController::class);

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
});

require __DIR__ . '/auth.php';
