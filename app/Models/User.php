<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
=======
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
>>>>>>> Cris
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
<<<<<<< HEAD
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';

    /**
     * @inheritdoc
     */
    public function freshTimestamp()
    {
        return new \MongoDB\BSON\UTCDateTime((int) round(microtime(true) * 1000));
    }
=======
    use HasApiTokens, Notifiable;

    // Esto le dice a Laravel que no use SQL, sino la conexión Mongo que definiste
    protected $connection = 'mongodb';
    protected $collection = 'users'; 
>>>>>>> Cris

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

<<<<<<< HEAD
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
=======
    public function getJWTIdentifier()
    {
        return (string) $this->_id;    
    }

>>>>>>> Cris
    public function getJWTCustomClaims()
    {
        return [];
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> Cris
