<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

// Modelo para Mensajes Individuales dentro del Chat
class Message extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'messages'; // Colección en MongoDB
    public $timestamps = false;
    
    use HasFactory;

    // Datos necesarios para crear un mensaje
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'created_at',
    ];

    // Relación: Cada mensaje pertenece a un hilo de conversación
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Relación: Cada mensaje tiene un usuario remitente
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}