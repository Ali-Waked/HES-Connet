<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
            $table->foreign('active_workspace_id')->references('id')->on('facilities')->nullOnDelete();
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->foreign('head_staff_id')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });

        // Schema::table('staff', function (Blueprint $table) {
        //     $table->foreign('staff_position_id')->references('id')->on('staff_positions')->nullOnDelete();
        // });

        Schema::table('facility_staff', function (Blueprint $table) {
            $table->foreign('position_id')->references('id')->on('positions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['active_workspace_id']);
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['head_staff_id']);
            $table->dropForeign(['city_id']);
        });

        // Schema::table('staff', function (Blueprint $table) {
        //     $table->dropForeign(['staff_position_id']);
        // });

        Schema::table('facility_staff', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
        });
    }
};
