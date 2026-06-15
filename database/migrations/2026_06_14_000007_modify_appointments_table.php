<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('date');

            $table->dateTime('start_at')->after('facility_id');
            $table->dateTime('end_at')->after('start_at');
            $table->text('notes')->nullable()->after('status');
            $table->text('cancellation_reason')->nullable()->after('notes');

            $table->index(['staff_id', 'start_at']);
            $table->index(['patient_id', 'start_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['staff_id', 'start_at']);
            $table->dropIndex(['patient_id', 'start_at']);
            $table->dropIndex(['status']);

            $table->dropColumn(['start_at', 'end_at', 'notes', 'cancellation_reason']);

            $table->dateTime('date')->after('facility_id');
        });
    }
};
