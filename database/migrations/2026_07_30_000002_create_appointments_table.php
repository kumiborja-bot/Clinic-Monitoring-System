<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->morphs('patient'); // patient_type & patient_id
            $table->string('email')->nullable();
            $table->dateTime('appointment_date');
            $table->string('clinic_location')->default('Main Campus Clinic');
            $table->foreignId('assigned_provider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_provider_name')->nullable();
            $table->text('reason_for_visit')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'attended', 'cancelled', 'rescheduled', 'completed'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
