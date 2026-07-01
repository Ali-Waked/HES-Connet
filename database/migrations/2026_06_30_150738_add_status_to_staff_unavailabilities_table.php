<?php

use App\Enums\StaffUnavailabilityStatus;
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
        Schema::table('staff_unavailabilities', function (Blueprint $table) {
            $table->string('status')
                ->default(StaffUnavailabilityStatus::PENDING->value)
                ->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_unavailabilities', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
