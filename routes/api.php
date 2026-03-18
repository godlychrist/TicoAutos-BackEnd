<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\MessageController;


/*
|--------------------------------------------------------------------------
| API Routes - TicoAutos
|--------------------------------------------------------------------------
|
| Rutas organizadas en 3 grupos:
| 1. Autenticación (públicas): registro y login
| 2. Vehículos (lectura pública, escritura protegida)
| 3. Mensajería (todas protegidas con JWT)
|
*/

// ── Usuario autenticado ──
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ── Autenticación (públicas) ──
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ── Vehículos: lectura pública (catálogo y detalle) ──
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

// ── Rutas protegidas (requieren token JWT válido) ──
Route::middleware('auth:api')->group(function () {

    // Vehículos: escritura (crear, editar, eliminar)
    Route::post('/vehicles', [VehicleController::class, 'createVehicle']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'editVehicle']);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'deleteVehicle']);
    
    // Conversaciones: crear, listar, ver detalle
    Route::post('/conversations', [MessageController::class, 'createConversation']);
    Route::get('/conversations', [MessageController::class, 'getConversations']);
    Route::get('/conversations/{id}', [MessageController::class, 'getConversation']);

    // Mensajes: enviar mensaje
    Route::post('/messages', [MessageController::class, 'store']);

});


