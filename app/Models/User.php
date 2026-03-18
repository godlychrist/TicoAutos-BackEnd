<?php

namespace App\Models;

use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Modelo de Usuario - Autenticación y gestión de identidad.
 *
 * Extiende el modelo de autenticación de MongoDB (jenssegers/mongodb)
 * e implementa JWTSubject para la generación de tokens JWT.
 * Solo maneja username + password (sin email).
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'username',
        'password',
    ];

    public $timestamps = false;

    /** Campos ocultos en las respuestas JSON por seguridad */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Identificador usado como 'sub' (subject) en el payload del JWT */
    public function getJWTIdentifier()
    {
        return (string) $this->_id;
    }

    /** Claims adicionales para el token JWT (vacío = solo los estándar) */
    public function getJWTCustomClaims()
    {
        return [];
    }
}