<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
=======
use App\Http\Controllers\VehiclesController;
use App\Http\Controllers\AuthController;
>>>>>>> origin/Cris

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

<<<<<<< HEAD
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/vehicles', [VehicleController::class, 'index']);
Route::post('/vehicles', [VehicleController::class, 'store']);
Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
=======

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/vehicles', [VehiclesController::class, 'store']);
Route::get('/vehicles', [VehiclesController::class, 'index']);
>>>>>>> origin/Cris
