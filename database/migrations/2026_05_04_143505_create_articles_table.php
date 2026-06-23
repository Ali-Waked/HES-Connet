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
            $table->foreignId('author_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->json('title');
            $table->string('slug')->unique();
            $table->string('status');
            $table->json('content');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('cover_image')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
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
