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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('staff_id')->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('facility_id')->constrained();
            $table->dateTime('date');
            $table->enum('status', ['booked','completed','cancelled','rescheduled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
