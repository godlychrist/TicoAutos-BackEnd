<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

// Modelo para Vehículos en venta (MongoDB)
class Vehicle extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicles'; // Colección en MongoDB

    // Campos permitidos para creación y edición masiva
    protected $fillable = [
        'brand',
        'model',
        'year',
        'price',
        'status',
        'image',
        'user_id'
    ];

    // Forzar tipos de datos específicos
    protected $casts = [
        'price' => 'float',
        'year' => 'integer',
        'user_id' => 'string'
    ];

    public $timestamps = false;

    // Relación: Un vehículo pertenece a un usuario vendedor
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
