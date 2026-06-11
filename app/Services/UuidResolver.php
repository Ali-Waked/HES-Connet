<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class UuidResolver
{
    public static function model(string $modelClass, string $uuid): Model
    {
        return $modelClass::query()
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public static function resolve(string $modelClass, string $uuid): int|string
    {
        return self::model($modelClass, $uuid)->getKey();
    }
}
