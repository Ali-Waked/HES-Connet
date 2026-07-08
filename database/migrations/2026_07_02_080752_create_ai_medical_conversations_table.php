<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_medical_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('language', 10)->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('message_count')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'last_activity_at'], 'conv_user_status_activity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_medical_conversations');
    }
};
