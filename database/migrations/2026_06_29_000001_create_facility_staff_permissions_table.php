<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_staff_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_staff_id')
                ->constrained('facility_staff')
                ->cascadeOnDelete();
            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['facility_staff_id', 'permission_id'], 'fs_permission_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_staff_permissions');
    }
};
