<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehiclesController;

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

// Vehículos (Nomenclature plural de Cris + Funciones nuevas)
Route::get('/vehicles', [VehiclesController::class, 'index']);
Route::post('/vehicles', [VehiclesController::class, 'store']);
Route::put('/vehicles/{id}', [VehiclesController::class, 'update']);
Route::delete('/vehicles/{id}', [VehiclesController::class, 'destroy']);
