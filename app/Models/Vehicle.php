<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

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
        'image_url',
        'user_id'
    ];

    public $timestamps = false;
}
