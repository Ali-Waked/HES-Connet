<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Dashboard\CityController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CityController::class, 'index']);
Route::post('/', [CityController::class, 'store']);
Route::get('{city}', [CityController::class, 'show']);
Route::put('{city}', [CityController::class, 'update']);
Route::delete('{city}', [CityController::class, 'destroy']);
