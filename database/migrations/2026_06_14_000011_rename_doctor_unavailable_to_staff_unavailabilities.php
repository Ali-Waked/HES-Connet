<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('doctor_unavailable', 'staff_unavailabilities');

        Schema::table('staff_unavailabilities', function (Blueprint $table) {
            $table->dropColumn('date');

            $table->dateTime('start_at')->after('staff_id');
            $table->dateTime('end_at')->after('start_at');
        });
    }

    public function down(): void
    {
        Schema::table('staff_unavailabilities', function (Blueprint $table) {
            $table->dropColumn(['start_at', 'end_at']);

            $table->date('date')->after('staff_id');
        });

        Schema::rename('staff_unavailabilities', 'doctor_unavailable');
    }
};
