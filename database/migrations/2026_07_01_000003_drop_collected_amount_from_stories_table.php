<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            if (Schema::hasColumn('stories', 'collected_amount')) {
                $table->dropColumn('collected_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->decimal('collected_amount', 10, 2)->default(0)->after('target_amount');
        });
    }
};
