<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * AuthController - Gestión de autenticación (registro y login).
 *
 * Utiliza JWT (tymon/jwt-auth) para generar tokens de acceso.
 * No extiende el Controller base de Laravel para evitar conflictos
 * con los middlewares por defecto; extiende directamente BaseController.
 */
class AuthController extends BaseController
{
    /**
     * Registrar un nuevo usuario.
     *
     * Valida username/password, hashea la contraseña con bcrypt
     * y almacena el usuario en MongoDB.
     */
    public function register(Request $request)
    {
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
            return response()->json([
                'message' => 'Error de conexión con MongoDB Atlas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar sesión y obtener un token JWT.
     *
     * Busca al usuario por username, verifica la contraseña con Hash::check
     * y genera un JWT válido para las peticiones autenticadas.
     * Retorna el token + datos básicos del usuario (username, id).
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            \Log::info('Intento de login para usuario: ' . $credentials['username']);

            $user = User::where('username', $credentials['username'])->first();
            if ($user) {
                \Log::info('Usuario encontrado. Tipo de password: ' . gettype($user->password));
            } else {
                \Log::info('Usuario no encontrado.');
            }

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                \Log::warning('Credenciales inválidas para: ' . $credentials['username']);
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }

            \Log::info('Clave verificada. Generando token...');

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

