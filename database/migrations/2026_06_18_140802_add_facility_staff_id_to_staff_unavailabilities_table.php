<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('staff_unavailabilities', 'facility_staff_id')) {
            Schema::table('staff_unavailabilities', function (Blueprint $table) {
                $table->foreignId('facility_staff_id')
                    ->after('id')
                    ->constrained('facility_staff')
                    ->cascadeOnDelete();

                $table->index('facility_staff_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('staff_unavailabilities', 'facility_staff_id')) {
            Schema::table('staff_unavailabilities', function (Blueprint $table) {
                $table->dropForeign(['facility_staff_id']);
                $table->dropIndex(['facility_staff_id']);
                $table->dropColumn('facility_staff_id');
            });
        }
    }
};
