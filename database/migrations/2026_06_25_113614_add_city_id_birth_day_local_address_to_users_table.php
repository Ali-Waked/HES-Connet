<?php

use App\Enums\GenderType;
use App\Enums\LocaleType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->enum(
                'locale',
                LocaleType::values()
            )->default(LocaleType::EN->value);
            $table->string('address')->nullable();
            $table->enum('gender', GenderType::values())->nullable();
            $table->string('phone', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birthday',
                'locale',
                'address',
                'gender',
                'phone',
            ]);
        });
    }
};
