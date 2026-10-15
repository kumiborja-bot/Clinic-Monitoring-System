<?php

use App\Models\Medicine;
use App\Models\MedicineLog;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add Supplier to Medicines
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('medicines', 'supplier')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('supplier', 255)
                    ->nullable()
                    ->after('description');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Create Stock Movement Ledger
        |--------------------------------------------------------------------------
        |
        | type = in
        | Medicine entered inventory.
        |
        | type = out
        | Medicine left inventory.
        |
        */

        if (!Schema::hasTable('medicine_stock_movements')) {
            Schema::create('medicine_stock_movements', function (Blueprint $table) {
                $table->id();

                $table->foreignId('medicine_id')
                    ->constrained('medicines')
                    ->cascadeOnDelete();

                $table->date('transaction_date');

                $table->enum('type', [
                    'in',
                    'out',
                ]);

                $table->unsignedInteger('quantity');

                $table->string('supplier', 255)
                    ->nullable();

                $table->string('requester', 255)
                    ->nullable();

                $table->text('notes')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'medicine_id',
                    'transaction_date',
                ]);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Reconstruct Existing Medicine History
        |--------------------------------------------------------------------------
        |
        | Your existing application already has:
        |
        | current stock
        | +
        | historical medicine dispensing logs
        |
        | Therefore:
        |
        | Original Quantity =
        | Current Stock + Previously Dispensed Quantity
        |
        | This keeps the CURRENT stock unchanged while allowing the new
        | Stock Card to reconstruct the existing dispensing history.
        |--------------------------------------------------------------------------
        */

        Medicine::query()
            ->orderBy('id')
            ->chunkById(100, function ($medicines) {

                foreach ($medicines as $medicine) {

                    $logs = MedicineLog::with('patient')
                        ->where(
                            'medicine_id',
                            $medicine->id
                        )
                        ->orderBy('dispensed_at')
                        ->orderBy('id')
                        ->get();

                    $historicalOut =
                        (int) $logs->sum('quantity');

                    $initialQuantity =
                        (int) $medicine->stock_quantity
                        + $historicalOut;

                    /*
                    |--------------------------------------------------------------------------
                    | Determine earliest known date
                    |--------------------------------------------------------------------------
                    */

                    $possibleDates = collect();

                    if ($medicine->created_at) {
                        $possibleDates->push(
                            Carbon::parse(
                                $medicine->created_at
                            )->toDateString()
                        );
                    }

                    if ($logs->isNotEmpty()) {
                        $possibleDates->push(
                            Carbon::parse(
                                $logs->first()->dispensed_at
                            )->toDateString()
                        );
                    }

                    $initialDate =
                        $possibleDates
                            ->filter()
                            ->sort()
                            ->first()
                        ?? now()->toDateString();

                    /*
                    |--------------------------------------------------------------------------
                    | Create reconstructed initial IN
                    |--------------------------------------------------------------------------
                    |
                    | Supplier intentionally stays blank because the old
                    | Category field cannot correctly tell us the supplier.
                    |--------------------------------------------------------------------------
                    */

                    if ($initialQuantity > 0) {
                        DB::table(
                            'medicine_stock_movements'
                        )->insert([
                            'medicine_id' =>
                                $medicine->id,

                            'transaction_date' =>
                                $initialDate,

                            'type' =>
                                'in',

                            'quantity' =>
                                $initialQuantity,

                            'supplier' =>
                                null,

                            'requester' =>
                                null,

                            'notes' =>
                                'Opening stock reconstructed from existing inventory data.',

                            'created_at' =>
                                $medicine->created_at ?? now(),

                            'updated_at' =>
                                now(),
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Convert Existing Dispensing Logs Into OUT Movements
                    |--------------------------------------------------------------------------
                    */

                    foreach ($logs as $log) {

                        $patient =
                            $log->patient;

                        $requester =
                            $patient
                                ? $patient->name
                                : 'Unknown';

                        $transactionDate =
                            $log->dispensed_at
                                ? Carbon::parse(
                                    $log->dispensed_at
                                )->toDateString()
                                : now()->toDateString();

                        DB::table(
                            'medicine_stock_movements'
                        )->insert([
                            'medicine_id' =>
                                $medicine->id,

                            'transaction_date' =>
                                $transactionDate,

                            'type' =>
                                'out',

                            'quantity' =>
                                (int) $log->quantity,

                            'supplier' =>
                                null,

                            'requester' =>
                                $requester,

                            'notes' =>
                                $log->notes,

                            'created_at' =>
                                $log->created_at ?? now(),

                            'updated_at' =>
                                $log->updated_at ?? now(),
                        ]);
                    }
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Completely Remove Category
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('medicines', 'category')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'medicine_stock_movements'
        );

        if (Schema::hasColumn('medicines', 'supplier')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('supplier');
            });
        }

        if (!Schema::hasColumn('medicines', 'category')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('category', 100)
                    ->nullable()
                    ->after('description');
            });
        }
    }
};