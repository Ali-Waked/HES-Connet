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
            $table->json('description')->nullable()->change();
        });

        DB::table('facilities')->get()->each(function ($item) {
              $update = [];
            if ($item->name && ! str_starts_with($item->name, '{')) {
                $update['name'] = json_encode(['en' => $item->name, 'ar' => $item->name]);
            }
            if ($item->description && ! str_starts_with($item->description, '{')) {
                $update['description'] = json_encode(['en' => $item->description, 'ar' => $item->description]);
            }
            if (! empty($update)) {
                DB::table('facilities')->where('id', $item->id)->update($update);
            }
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
        });
    }
};
