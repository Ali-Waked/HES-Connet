<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->json('name')->change();
        });

        DB::table('facilities')->get()->each(function ($item) {
            if ($item->name && !str_starts_with($item->name, '{')) {
                DB::table('facilities')
                    ->where('id', $item->id)
                    ->update([
                        'name' => json_encode(['en' => $item->name, 'ar' => $item->name])
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
