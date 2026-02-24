<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->foreignUuid('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignUuid('doctor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('diagnosis');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
