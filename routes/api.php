<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Devuelve el usuario autenticado actualmente
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------------------------------------------------------------
// Rutas Públicas
// ------------------------------------------------------------------------

// Autenticación de usuarios
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Consulta de vehículos (Público)
Route::get('/vehicles', [VehicleController::class, 'index']);      // Listar todos
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);  // Detalle de vehículo


// ------------------------------------------------------------------------
// Rutas Protegidas (Requieren Token JWT/Sanctum)
// ------------------------------------------------------------------------
Route::middleware('auth:api')->group(function () {
    
    // Gestión de Vehículos
    Route::post('/vehicles', [VehicleController::class, 'createVehicle']);       // Crear
    Route::put('/vehicles/{id}', [VehicleController::class, 'editVehicle']);     // Actualizar
    Route::delete('/vehicles/{id}', [VehicleController::class, 'deleteVehicle']); // Eliminar
    
    // Gestión de Conversaciones
    Route::post('/conversations', [MessageController::class, 'createConversation']); // Iniciar chat
    Route::get('/conversations', [MessageController::class, 'getConversations']);    // Listar chats
    Route::get('/conversations/{id}', [MessageController::class, 'getConversation']); // Ver chat

    // Envío de Mensajes
    Route::post('/messages', [MessageController::class, 'store']); // Enviar mensaje
});
