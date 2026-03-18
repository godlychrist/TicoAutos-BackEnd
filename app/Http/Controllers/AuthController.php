<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    // Registra un nuevo usuario en la base de datos
    public function register(Request $request)
    {
        // 1. Validar campos obligatorios
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 2. Crear usuario y encriptar contraseña
            $userId = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => '¡Usuario registrado con éxito en MongoDB!',
                'user_id' => $userId
            ], 201);

        } catch (\Exception $e) {
            // 3. Manejo de errores de conexión (MongoDB)
            return response()->json([
                'message' => 'Error de conexión con MongoDB Atlas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Autentica un usuario y genera un Token JWT
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            \Log::info('Intento de login para usuario: ' . $credentials['username']);

            // 1. Buscar usuario por nombre de usuario
            $user = User::where('username', $credentials['username'])->first();
            if ($user) {
                \Log::info('Usuario encontrado. Tipo de password: ' . gettype($user->password));
            } else {
                \Log::info('Usuario no encontrado.');
            }

            // 2. Verificar contraseña
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                \Log::warning('Credenciales inválidas para: ' . $credentials['username']);
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }

            \Log::info('Clave verificada. Generando token...');

            // 3. Generar token de sesión
            $token = JWTAuth::fromUser($user);
            \Log::info('Token generado con éxito');

            return response()->json([
                'message' => 'Login exitoso',
                'token' => $token,
                'user' => [
                    'username' => $user->username,
                    'id' => (string) $user->_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error crítico en el login',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
