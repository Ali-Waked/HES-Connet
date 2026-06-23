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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('head_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
