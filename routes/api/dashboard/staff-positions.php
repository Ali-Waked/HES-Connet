<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Dashboard\StaffPositionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StaffPositionController::class, 'index']);
Route::post('/', [StaffPositionController::class, 'store']);
Route::get('{staff_position}', [StaffPositionController::class, 'show']);
Route::put('{staff_position}', [StaffPositionController::class, 'update']);
Route::delete('{staff_position}', [StaffPositionController::class, 'destroy']);
