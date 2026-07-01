<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_reviews', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('comment');
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete()->after('reply');
            $table->timestamp('replied_at')->nullable()->after('replied_by');
            // $table->dropUnique('platform_reviews_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('platform_reviews', function (Blueprint $table) {
            $table->dropColumn(['reply', 'replied_by', 'replied_at']);
            $table->unique('user_id');
        });
    }
};
