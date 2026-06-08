<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_name_unique');
            $table->json('name')->change();
            $table->json('name')->change();
        });

        DB::table('tags')->get()->each(function ($item) {
            if ($item->name && ! str_starts_with($item->name, '{')) {
                DB::table('tags')
                    ->where('id', $item->id)
                    ->update([
                        'name' => json_encode(['en' => $item->name, 'ar' => $item->name]),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
