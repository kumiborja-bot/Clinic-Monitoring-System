<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->foreignId('medicine_id')->nullable()->after('medication_given')->constrained('medicines')->nullOnDelete();
            $table->integer('medicine_quantity')->nullable()->after('medicine_id');
            $table->foreignId('appointment_id')->nullable()->after('medicine_quantity')->constrained('appointments')->nullOnDelete();
        });

        Schema::table('medicine_logs', function (Blueprint $table) {
            $table->foreignId('treatment_id')->nullable()->after('medicine_id')->constrained('treatments')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->after('treatment_id')->constrained('appointments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['medicine_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropColumn(['medicine_id', 'medicine_quantity', 'appointment_id']);
        });

        Schema::table('medicine_logs', function (Blueprint $table) {
            $table->dropForeign(['treatment_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropColumn(['treatment_id', 'appointment_id']);
        });
    }
};
