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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('auth_id')->constrained('staff')->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', [
                'draft',
                'pending_review',
                'published',
                'archived',
                'rejected',
            ])->default('draft');
            $table->text('content');
            $table->foreignId('category_id')->constrained();
            $table->integer('views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
