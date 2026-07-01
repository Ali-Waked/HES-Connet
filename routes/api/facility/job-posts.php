<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Facility\JobPostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [JobPostController::class, 'index']);
Route::post('/', [JobPostController::class, 'store']);
Route::get('stats', [JobPostController::class, 'stats']);
Route::get('{jobPost}', [JobPostController::class, 'show']);
Route::put('{jobPost}', [JobPostController::class, 'update']);
Route::delete('{jobPost}', [JobPostController::class, 'destroy']);
