<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->json('specialization')->nullable()->change();
            $table->json('bio')->nullable()->change();
        });

        DB::table('staff')->get()->each(function ($item) {
            $update = [];
            if ($item->specialization && !str_starts_with($item->specialization, '{')) {
                $update['specialization'] = json_encode(['en' => $item->specialization, 'ar' => $item->specialization]);
            }
            if ($item->bio && !str_starts_with($item->bio, '{')) {
                $update['bio'] = json_encode(['en' => $item->bio, 'ar' => $item->bio]);
            }
            
            if (!empty($update)) {
                DB::table('staff')->where('id', $item->id)->update($update);
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('specialization')->nullable()->change();
            $table->text('bio')->nullable()->change();
        });
    }
};
