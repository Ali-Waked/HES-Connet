<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_medical_conversations', function (Blueprint $table) {
            $table->json('extracted_symptoms')->nullable()->after('summary');
            $table->string('estimated_specialty')->nullable()->after('extracted_symptoms');
            $table->string('urgency')->nullable()->after('estimated_specialty');
            $table->decimal('confidence', 3, 2)->nullable()->after('urgency');
            $table->string('triage_status')->default('collecting')->after('confidence');
            $table->timestamp('recommended_at')->nullable()->after('triage_status');
        });
    }

    public function down(): void
    {
        Schema::table('ai_medical_conversations', function (Blueprint $table) {
            $table->dropColumn([
                'extracted_symptoms',
                'estimated_specialty',
                'urgency',
                'confidence',
                'triage_status',
                'recommended_at',
            ]);
        });
    }
};
