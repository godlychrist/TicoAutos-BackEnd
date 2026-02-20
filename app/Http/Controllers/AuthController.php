<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:mongodb.users,username|max:255',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);
        
        return response()->json([
            'message' => 'Usuario registrado con exito',
            'user' => $user
        ], 201);
    }
}
