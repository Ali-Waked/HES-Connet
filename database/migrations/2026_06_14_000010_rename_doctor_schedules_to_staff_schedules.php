<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('doctor_schedules', 'staff_schedules');

        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->foreignId('facility_id')->after('staff_id')->constrained();

            $table->unique(
                ['staff_id', 'facility_id', 'day_of_week', 'start_time', 'end_time'],
                'staff_schedules_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropColumn('facility_id');

            $table->dropUnique('staff_schedules_unique');
        });

        Schema::rename('staff_schedules', 'doctor_schedules');
    }
};
