<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_files', function (Blueprint $table) {
            $table->unique(['appointment_id', 'file_id']);
        });
    }

    public function down(): void
    {
        Schema::table('appointment_files', function (Blueprint $table) {
            $table->dropUnique(['appointment_id', 'file_id']);
        });
    }
};
