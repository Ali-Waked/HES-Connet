<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->json('content')->change();
        });

        DB::table('stories')->get()->each(function ($item) {
            if ($item->content && ! str_starts_with($item->content, '{')) {
                DB::table('stories')
                    ->where('id', $item->id)
                    ->update([
                        'content' => json_encode(['en' => $item->content, 'ar' => $item->content]),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->text('content')->change();
        });
    }
};
