<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('staff_position_id')
                ->nullable()
                ->after('consultation_fee')
                ->constrained('staff_positions')
                ->nullOnDelete()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['staff_position_id']);
            $table->dropColumn('staff_position_id');
        });
    }
};
