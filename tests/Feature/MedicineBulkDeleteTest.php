<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_deletion_preserves_unselected_medicines_and_all_deletion_clears_remaining_records(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $medicines = collect(['First', 'Second', 'Keep'])->map(function ($name) use ($user) {
            $medicine = Medicine::create(['name' => $name, 'supplier' => 'Supplier', 'stock_quantity' => 10, 'unit' => 'tablet']);
            MedicineLog::create([
                'medicine_id' => $medicine->id, 'patient_type' => Student::class, 'patient_id' => 999,
                'quantity' => 1, 'dispensed_by' => $user->id, 'dispensed_at' => now(),
            ]);
            return $medicine;
        });

        $this->delete(route('medicines.bulk-delete'), ['ids' => []])->assertSessionHasErrors('ids');
        $this->delete(route('medicines.bulk-delete'), ['ids' => [$medicines[0]->id, 99999]])->assertSessionHasErrors('ids.1');
        $this->assertDatabaseCount('medicines', 3);

        $this->delete(route('medicines.bulk-delete'), ['ids' => $medicines->take(2)->pluck('id')->all()])->assertRedirect(route('medicines.index'));
        $this->assertDatabaseCount('medicines', 1);
        $this->assertDatabaseHas('medicines', ['id' => $medicines[2]->id]);
        $this->assertDatabaseCount('medicine_logs', 1);
        $this->assertDatabaseCount('medicine_stock_movements', 1);

        $this->delete(route('medicines.delete-all'))->assertRedirect(route('medicines.index'));
        $this->assertDatabaseCount('medicines', 0);
        $this->assertDatabaseCount('medicine_logs', 0);
        $this->assertDatabaseCount('medicine_stock_movements', 0);
    }
}
