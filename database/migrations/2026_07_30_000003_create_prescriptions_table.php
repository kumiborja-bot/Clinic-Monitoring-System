<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->morphs('patient');
            $table->foreignId('prescribed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prescriber_name')->nullable();
            $table->json('medication_details'); // array of { medicine_name, dosage, frequency, duration, instructions }
            $table->text('notes')->nullable();
            $table->dateTime('issued_at');
            $table->enum('status', ['issued', 'dispensed', 'cancelled'])->default('issued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
