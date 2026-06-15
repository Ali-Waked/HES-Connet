<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Dashboard\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index']);
Route::post('/', [PageController::class, 'store']);
Route::get('{page}', [PageController::class, 'show']);
Route::put('{page}', [PageController::class, 'update']);
Route::delete('{page}', [PageController::class, 'destroy']);
