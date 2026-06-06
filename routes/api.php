<?php

use App\Http\Controllers\Api\Dashboard\DepartmentController;
use App\Http\Controllers\Api\Dashboard\FacilityController;
use App\Http\Controllers\Api\Dashboard\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth')->prefix('dashboard')->group(function(){
    Route::apiResources([
        'organizations'=> OrganizationController::class,
        'facilities'=> FacilityController::class,
        'departments' => DepartmentController::class,
    ]);
});