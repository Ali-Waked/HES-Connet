<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_unavailabilities', function (Blueprint $table) {
            $table->foreignId('facility_staff_id')
                ->after('id') // اختياري حسب ترتيبك
                ->constrained('facility_staff') // أو اسم جدولك الصحيح
                ->cascadeOnDelete();

            $table->index('facility_staff_id');
        });
    }

    public function down(): void
    {
        Schema::table('staff_unavailabilities', function (Blueprint $table) {
            $table->dropForeign(['facility_staff_id']);
            $table->dropIndex(['facility_staff_id']);
            $table->dropColumn('facility_staff_id');
        });
    }
};
