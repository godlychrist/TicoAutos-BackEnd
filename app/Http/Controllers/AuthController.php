<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Usaremos DB directamente
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function register(Request $request) 
    {
        // 1. Validamos los datos
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $userId = DB::connection('mongodb')
                ->collection('users')
                ->insertGetId([
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'message' => '¡Usuario registrado con éxito en MongoDB!',
                'user_id' => $userId
            ], 201);

        } catch (\Exception $e) {
            // 3. Si algo sale mal con la conexión a Atlas, aquí lo veremos
            return response()->json([
                'message' => 'Error de conexión con MongoDB Atlas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
        
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            // 1. Buscamos al usuario usando DB::connection('mongodb')
            // Esto NO dispara el error de prepare() porque no usa PDO
            $userDoc = DB::connection('mongodb')
                ->collection('users')
                ->where('username', $credentials['username'])
                ->first();

            // 2. Verificamos si existe y la clave
            if (!$userDoc || !Hash::check($credentials['password'], $userDoc['password'])) {
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }

            // 3. Convertimos el array de MongoDB en un objeto para que JWT lo entienda
            $user = new \App\Models\User();
            foreach ($userDoc as $key => $value) {
                $user->{$key} = $value;
            }

            // 4. Generamos el token
            $token = JWTAuth::fromUser($user);

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
