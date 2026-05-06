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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('patient_id')->constrained();
            $table->text('content');
            $table->enum('status', ['pending','approved','rejected']);
            $table->boolean('is_fundraising')->default(false);
            $table->decimal('target_amount',10,2)->nullable();
            $table->decimal('collected_amount',10,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
