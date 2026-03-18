<?php

namespace App\Models;

use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

// Modelo de Usuario autenticable (MongoDB) con soporte JWT
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users'; // Colección en MongoDB

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'username',
        'password',
    ];

    public $timestamps = false;

    // Campos ocultos en las respuestas JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Obtener identificador único para el token JWT
    public function getJWTIdentifier()
    {
        return (string) $this->_id;
    }

    // Reclamaciones personalizadas (opcional) para JWT
    public function getJWTCustomClaims()
    {
        return [];
    }
}