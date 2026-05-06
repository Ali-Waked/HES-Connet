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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('facility_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('title');
            $table->text('description');
            $table->enum('apply_method',['email','link']);
            $table->string('apply_value');
            $table->enum('status',['pending','approved']);
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
