<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_staff_id')->constrained('facility_staff')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->timestamps();

            $table->index(['facility_staff_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_unavailabilities');
    }
};
