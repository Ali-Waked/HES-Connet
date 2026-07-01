<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('user_name')->nullable()->after('user_id');
            $table->string('user_type', 50)->nullable()->after('user_name');
            $table->string('request_method', 10)->nullable()->after('user_agent');
            $table->text('request_url')->nullable()->after('request_method');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['user_name', 'user_type', 'request_method', 'request_url']);
        });
    }
};
