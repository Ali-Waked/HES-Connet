<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

         DB::table('users')->get()->each(function ($item) {
            $update = [];
            if ($item->name && !str_starts_with($item->name, '{')) {
                $update['name'] = json_encode(['en' => $item->name, 'ar' => $item->name]);
            }

            if (!empty($update)) {
                DB::table('users')->where('id', $item->id)->update($update);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('name')->change();
            
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
