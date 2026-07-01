<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->whereDoesntHave('profile')
            ->each(function (User $user) {
                $user->profile()->create([
                    'phone' => $user->getRawOriginal('phone'),
                    'gender' => $user->getRawOriginal('gender'),
                    'birth_date' => $user->getRawOriginal('birth_date'),
                    'address' => $user->getRawOriginal('address'),
                    'profile_image' => $user->getRawOriginal('avatar'),
                    'cover_image' => $user->getRawOriginal('cover_image'),
                ]);
            });

        if (Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'avatar',
                    'cover_image',
                    'gender',
                    'birth_date',
                    'address',
                    'phone',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('last_seen_at');
            $table->string('cover_image')->nullable()->after('avatar');
            $table->string('phone', 30)->nullable()->after('cover_image');
            $table->enum('gender', ['male', 'female'])->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('address')->nullable()->after('birth_date');
        });

        User::query()
            ->whereHas('profile')
            ->each(function (User $user) {
                $profile = $user->profile;
                $user->update([
                    'avatar' => $profile->getRawOriginal('profile_image'),
                    'cover_image' => $profile->getRawOriginal('cover_image'),
                    'gender' => $profile->getRawOriginal('gender'),
                    'birth_date' => $profile->getRawOriginal('birth_date'),
                    'address' => $profile->getRawOriginal('address'),
                    'phone' => $profile->getRawOriginal('phone'),
                ]);
            });
    }
};
