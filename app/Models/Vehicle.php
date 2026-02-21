<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable;

    // Esto le dice a Laravel que no use SQL, sino la conexión Mongo que definiste
    protected $connection = 'mongodb';
    protected $collection = 'vehicles'; 

    protected $fillable = [
        'brand',
        'year',
        'model',
        'price',
        'status',
        'image',
        'user_id'

    ];

}