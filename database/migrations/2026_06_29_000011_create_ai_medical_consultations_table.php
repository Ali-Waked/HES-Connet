<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_medical_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->text('symptoms');
            $table->text('analysis')->nullable();
            $table->string('urgency')->nullable();
            $table->json('recommended_specialties')->nullable();
            $table->json('recommended_doctors')->nullable();
            $table->json('follow_up_questions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_medical_consultations');
    }
};
