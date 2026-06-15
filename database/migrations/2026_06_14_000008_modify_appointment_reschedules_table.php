<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_reschedules', function (Blueprint $table) {
            $table->dropColumn(['old_date', 'new_date']);

            $table->dropForeign(['appointment_id']);
            $table->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();

            $table->dateTime('old_start_at')->after('appointment_id');
            $table->dateTime('old_end_at')->after('old_start_at');
            $table->dateTime('new_start_at')->after('old_end_at');
            $table->dateTime('new_end_at')->after('new_start_at');
            $table->text('reason')->nullable()->after('new_end_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_reschedules', function (Blueprint $table) {
            $table->dropColumn(['old_start_at', 'old_end_at', 'new_start_at', 'new_end_at', 'reason']);

            $table->dropForeign(['appointment_id']);
            $table->foreign('appointment_id')->references('id')->on('appointments');

            $table->dateTime('old_date')->after('appointment_id');
            $table->dateTime('new_date')->after('old_date');
        });
    }
};
