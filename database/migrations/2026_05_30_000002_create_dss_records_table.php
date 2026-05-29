<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('dss_records', function (Blueprint $table) {
            $table->id();
            $table->string('diagnosis');
            $table->text('suggestion');
            $table->string('follow_up')->nullable();
            $table->string('medication')->nullable();
            $table->string('duration_unit')->nullable();
            $table->integer('duration_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dss_records');
    }
};
