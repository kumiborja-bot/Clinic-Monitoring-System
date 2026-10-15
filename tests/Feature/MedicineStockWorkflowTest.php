<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Student;
use App\Models\User;
use App\Services\MedicineStockCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineStockWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_inventory_stock_in_and_dispensing_keep_stock_card_balanced(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
        $department = Department::create(['code' => 'TEST', 'name' => 'Test', 'full_name' => 'Test Department']);
        $student = Student::create(['name' => 'Test Patient', 'student_id_number' => 'TEST1', 'department_id' => $department->id]);
        $data = ['name' => 'Test Medicine', 'supplier' => 'Test Supplier', 'unit' => 'tablet', 'stock_quantity' => 10, 'initial_stock_date' => today()->toDateString()];
        $this->post(route('medicines.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $medicine = Medicine::firstOrFail();
        $this->assertDatabaseHas('medicine_stock_movements', ['medicine_id' => $medicine->id, 'type' => 'in', 'quantity' => 10, 'supplier' => 'Test Supplier']);

        $this->put(route('medicines.update', $medicine), [...$data, 'supplier' => 'Updated Supplier', 'stock_quantity' => 999])->assertSessionHasNoErrors();
        $this->assertSame(10, $medicine->fresh()->stock_quantity);
        $this->assertSame('Updated Supplier', $medicine->fresh()->supplier);

        $this->post(route('medicines.stock-in'), ['medicine_id' => $medicine->id, 'supplier' => 'Delivery Supplier', 'quantity' => 5, 'transaction_date' => today()->toDateString()])->assertSessionHasNoErrors();
        $dispense = ['medicine_id' => $medicine->id, 'patient_type' => 'student', 'patient_id' => $student->id, 'quantity' => 2];
        $this->post(route('medicines.dispense'), $dispense)->assertSessionHasNoErrors();
        $this->postJson(route('offline-queue.sync'), ['type' => 'medicine_dispense', 'data' => $dispense])->assertOk();
        $this->post(route('treatments.store'), ['patient_type' => 'student', 'patient_id' => $student->id, 'medicine_id' => $medicine->id, 'medicine_quantity' => 1, 'diagnosis' => 'Test', 'status' => 'completed'])->assertSessionHasNoErrors();

        $this->assertSame(10, $medicine->fresh()->stock_quantity);
        $this->assertDatabaseCount('medicine_logs', 3);
        $this->assertDatabaseCount('medicine_stock_movements', 5);
        $card = app(MedicineStockCardService::class)->build($medicine->fresh());
        $this->assertSame(10, $card['transactions']->last()['balance']);

        $this->post(route('medicines.dispense'), [...$dispense, 'quantity' => 999])->assertSessionHasErrors('quantity');
        $this->assertSame(10, $medicine->fresh()->stock_quantity);
        $this->assertDatabaseCount('medicine_stock_movements', 5);

        $appointment = Appointment::create([
            'patient_type' => Student::class, 'patient_id' => $student->id,
            'appointment_date' => now(), 'status' => 'scheduled',
        ]);
        $this->post(route('appointments.record-consultation', $appointment), [
            'diagnosis' => 'Test consultation', 'medicine_id' => $medicine->id, 'medicine_quantity' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertSame(9, $medicine->fresh()->stock_quantity);
        $this->assertDatabaseCount('medicine_stock_movements', 6);
        $card = app(MedicineStockCardService::class)->build($medicine->fresh());
        $this->assertSame(9, $card['transactions']->last()['balance']);

        foreach (['medicines.index', 'medicines.logs', 'medicines.stock-in.form', 'medicines.dispense.form'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('medicines.stock-card', $medicine))->assertOk();
    }

    public function test_offline_creation_preserves_supplier_and_initial_stock_date(): void
    {
        $this->actingAs(User::factory()->create());
        $date = today()->subDays(2)->toDateString();
        $this->postJson(route('offline-queue.sync'), ['type' => 'medicine', 'data' => [
            'name' => 'Offline Medicine', 'supplier' => 'Offline Supplier', 'unit' => 'tablet',
            'stock_quantity' => 7, 'initial_stock_date' => $date,
        ]])->assertOk();
        $this->assertDatabaseHas('medicines', ['supplier' => 'Offline Supplier', 'stock_quantity' => 7]);
        $movement = Medicine::firstOrFail()->stockMovements()->sole();
        $this->assertSame($date, $movement->transaction_date->toDateString());
        $this->assertSame('in', $movement->type);
        $this->assertEquals(7, $movement->quantity);
    }

    public function test_stock_card_starts_with_a_dated_transaction_and_preserves_carried_balance(): void
    {
        $medicine = Medicine::create(['name' => 'Test Medicine', 'supplier' => 'Test Supplier', 'stock_quantity' => 13, 'unit' => 'tablet']);
        $service = app(MedicineStockCardService::class);
        $this->assertCount(0, $service->build($medicine)['transactions']);
        $medicine->stockMovements()->create([
            'transaction_date' => '2026-09-01', 'type' => 'in', 'quantity' => 10, 'supplier' => 'Test Supplier',
        ]);
        $medicine->stockMovements()->create([
            'transaction_date' => '2026-09-03', 'type' => 'in', 'quantity' => 3, 'supplier' => 'Test Supplier',
        ]);
        $card = $service->build($medicine);
        $this->assertCount(2, $card['transactions']);
        $this->assertSame('2026-09-01', $card['transactions']->first()['date']);
        $this->assertSame(10, $card['transactions']->first()['balance']);
        $filtered = $service->build($medicine, '2026-09-02');
        $this->assertSame(10, $filtered['openingBalance']);
        $this->assertCount(1, $filtered['transactions']);
        $this->assertSame('2026-09-03', $filtered['transactions']->first()['date']);
        $this->assertSame(13, $filtered['transactions']->first()['balance']);
        $this->assertFileExists(public_path('images/mmaci-logo.png'));
    }
}
