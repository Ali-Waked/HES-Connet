<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('symptom_doctor', 'doctor_symptom');

        Schema::table('doctor_symptom', function (Blueprint $table) {
            $table->unique(['symptom_id', 'staff_id'], 'doctor_symptom_unique');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_symptom', function (Blueprint $table) {
            $table->dropUnique('doctor_symptom_unique');
        });

        Schema::rename('doctor_symptom', 'symptom_doctor');
    }
};
