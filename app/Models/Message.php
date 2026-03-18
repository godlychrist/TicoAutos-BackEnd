<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Modelo de Mensaje - Comunicación entre compradores y vendedores.
 *
 * Cada mensaje pertenece a una conversación y tiene un remitente (sender).
 * Se almacena el timestamp de creación manualmente en 'created_at'.
 */
class Message extends Model
{

    protected $connection = 'mongodb';
    protected $collection = 'messages';
    public $timestamps = false;
    
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'created_at',
    ];

    /** Relación inversa: la conversación a la que pertenece este mensaje */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /** Relación inversa: el usuario que envió este mensaje */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

}