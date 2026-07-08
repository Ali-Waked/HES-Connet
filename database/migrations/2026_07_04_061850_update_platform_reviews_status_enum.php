<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('platform_reviews', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
        } else {
            DB::statement("ALTER TABLE platform_reviews MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'hidden') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('platform_reviews', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
        } else {
            DB::statement("ALTER TABLE platform_reviews MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
        }
    }
};
