<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

// Modelo para Conversaciones (Inbox/Chats combinados)
class Conversation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'conversations'; // Colección en MongoDB
    public $timestamps = false;

    use HasFactory;

    // Campos permitidos para inserción masiva
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'vehicle_id',
        'last_message',
        'last_message_at'
    ];

    // Relación: La conversación tiene un usuario comprador
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Relación: La conversación tiene un usuario vendedor
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Relación: La conversación gira en torno a un vehículo específico
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relación: Una conversación contiene múltiples mensajes
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}