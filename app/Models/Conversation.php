<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Modelo de Conversación - Hilo de chat entre un comprador y un vendedor.
 *
 * Cada conversación está vinculada a un vehículo específico y conecta
 * exactamente a dos usuarios: buyer (comprador) y seller (vendedor).
 * Almacena el último mensaje y su timestamp para ordenamiento en la UI.
 */
class Conversation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'conversations';
    public $timestamps = false;


    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'vehicle_id',
        'last_message',
        'last_message_at'
    ];

    /** Relación: el usuario comprador que inició la conversación */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /** Relación: el usuario vendedor (dueño del vehículo) */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /** Relación: el vehículo sobre el cual se está conversando */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Relación: todos los mensajes dentro de esta conversación */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}