<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Autenticación (Basado en la estructura de Cris)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/vehicles', [VehicleController::class, 'index']);

// Vehículos (Nomenclature plural de Cris + Funciones nuevas)
Route::middleware('auth:api')->group(function () {
    Route::post('/vehicles', [VehicleController::class, 'createVehicle']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'editVehicle']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'deleteVehicle']);
});