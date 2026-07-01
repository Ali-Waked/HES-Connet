<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('doctor_symptom');

        Schema::create('facility_staff_symptom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_staff_id')->constrained('facility_staff')->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['facility_staff_id', 'symptom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_staff_symptom');

        Schema::create('doctor_symptom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['symptom_id', 'staff_id']);
        });
    }
};
