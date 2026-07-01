<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('query');
            $table->string('type')->nullable()->index();
            $table->json('filters')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('query');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
