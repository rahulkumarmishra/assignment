<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DrugSearchController;
use App\Http\Controllers\UserDrugController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/drugs/search', [DrugSearchController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/drugs', [UserDrugController::class, 'store']);
    Route::delete('/user/drugs/{rxcui}', [UserDrugController::class, 'destroy']);
    Route::get('/user/drugs', [UserDrugController::class, 'index']);
});
