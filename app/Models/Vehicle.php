<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Modelo de Vehículo - Entidad principal del marketplace.
 *
 * Representa un vehículo publicado para venta/exhibición.
 * Cada vehículo pertenece a un usuario (vendedor) y contiene
 * los datos básicos: marca, modelo, año, precio, estado e imagen.
 */
class Vehicle extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'vehicles';

    protected $fillable = [
        'brand',
        'model',
        'year',
        'price',
        'status',
        'image',
        'user_id'
    ];

    /** Casting automático para asegurar tipos correctos en consultas y respuestas */
    protected $casts = [
        'price' => 'float',
        'year' => 'integer',
        'user_id' => 'string'
    ];

    public $timestamps = false;

    /** Relación inversa: el usuario (vendedor) que publicó este vehículo */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
