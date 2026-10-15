<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MedicineSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_pages_load_stocked_medicines_with_suppliers(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $medicine = Medicine::create([
            'name' => 'Stocked medicine',
            'supplier' => 'Test supplier',
            'stock_quantity' => 10,
            'unit' => 'tablet',
        ]);
        Medicine::create([
            'name' => 'Unavailable medicine',
            'stock_quantity' => 0,
        ]);

        foreach (['/appointments' => 'Appointments/Index', '/treatments/create' => 'Treatments/Create'] as $url => $component) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component($component)
                ->has('medicines', 1)
                ->where('medicines.0.id', $medicine->id)
                ->where('medicines.0.supplier', 'Test supplier')
                ->missing('medicines.0.category')
            );
        }
    }
}
