<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('content')->change();
        });

        DB::table('announcements')->get()->each(function ($item) {
            $update = [];
            if ($item->title && ! str_starts_with($item->title, '{')) {
                $update['title'] = json_encode(['en' => $item->title, 'ar' => $item->title]);
            }
            if ($item->content && ! str_starts_with($item->content, '{')) {
                $update['content'] = json_encode(['en' => $item->content, 'ar' => $item->content]);
            }

            if (! empty($update)) {
                DB::table('announcements')->where('id', $item->id)->update($update);
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('title')->change();
            $table->text('content')->change();
        });
    }
};
