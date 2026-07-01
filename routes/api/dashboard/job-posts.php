<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Dashboard\JobPostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [JobPostController::class, 'index']);
Route::get('stats', [JobPostController::class, 'stats']);
Route::get('{jobPost}', [JobPostController::class, 'show']);
Route::patch('{jobPost}/approve', [JobPostController::class, 'approve']);
Route::patch('{jobPost}/reject', [JobPostController::class, 'reject']);
Route::delete('{jobPost}', [JobPostController::class, 'destroy']);
