<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('platform_reviews')->cascadeOnDelete();
            $table->enum('type', ['auto_reply', 'admin_reply']);
            $table->string('sent_to', 255);
            $table->timestamp('sent_at')->useCurrent();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_notifications');
    }
};
