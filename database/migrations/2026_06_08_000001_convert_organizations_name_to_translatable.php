<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->json('name')->change();
        });

        DB::table('organizations')->get()->each(function ($item) {
            if ($item->name && !str_starts_with($item->name, '{')) {
                DB::table('organizations')
                    ->where('id', $item->id)
                    ->update([
                        'name' => json_encode(['en' => $item->name, 'ar' => $item->name])
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
