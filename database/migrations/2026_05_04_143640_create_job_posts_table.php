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
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('content');
            $table->enum('apply_method', ['email', 'link']);
            $table->string('apply_value');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary', 'internship', 'volunteer', 'remote']);
            $table->enum('experience_level', ['entry', 'junior', 'mid', 'senior', 'lead']);
            $table->string('location')->nullable();
            $table->decimal('salary_from', 10, 2)->nullable();
            $table->decimal('salary_to', 10, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->boolean('is_salary_visible')->default(false);
            $table->unsignedInteger('vacancies')->default(1);
            $table->unsignedInteger('views')->default(0);
            $table->boolean('featured')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->text('rejected_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->date('end_date');
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['facility_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('employment_type');
            $table->index('experience_level');
            $table->index('featured');
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
