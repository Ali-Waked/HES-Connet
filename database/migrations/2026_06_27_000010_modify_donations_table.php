<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (Schema::hasColumn('donations', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (! Schema::hasColumn('donations', 'uuid')) {
                $table->uuid('uuid')->after('id')->unique();
            }
            if (! Schema::hasColumn('donations', 'currency')) {
                $table->string('currency', 3)->default('SAR')->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('payment_method')->nullable();
            $table->dropColumn(['uuid', 'currency']);
        });
    }
};
