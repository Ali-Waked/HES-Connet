<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payments');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('donation_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('payable');
            $table->string('provider');
            $table->string('transaction_ref')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('SAR');
            $table->string('status');
            $table->json('response_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
