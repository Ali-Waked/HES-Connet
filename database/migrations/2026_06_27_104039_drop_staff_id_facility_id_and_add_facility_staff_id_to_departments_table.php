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
        Schema::table('departments', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['head_id']);
            $table->dropColumn('head_id');
            $table->dropForeign(['facility_id']);
            $table->dropColumn('facility_id');

            // Add new foreign key
            $table->foreignId('head_facility_staff_id')
                ->nullable()
                ->after('image')
                ->constrained('facility_staff')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Remove new foreign key
            $table->dropForeign(['head_facility_staff_id']);
            $table->dropColumn('head_facility_staff_id');

            // Restore old foreign key
            $table->foreignId('head_id')
                ->nullable()
                ->after('image')
                ->constrained('staff')
                ->nullOnDelete();
        });
    }
};
