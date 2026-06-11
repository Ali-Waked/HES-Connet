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
        Schema::table('facilities', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'active',
                'inactive',
                'temporarily_closed',
                'permanently_closed',
            ])->default('pending')->after('facility_type');

            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
                'suspended',
            ])->default('pending')->after('status');
            $table->foreignId('created_by')->after('organization_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['status', 'approval_status', 'created_by']);
        });
    }
};
