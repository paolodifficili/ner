<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $table = 'config';
    protected $primaryKey = 'id';

    protected $fillable = [ 
        'uuid',
        'engine',
        'engine_version',
        'api',
        'api_status',
        'api_config',
        'api_service',
        'type',
        'status',
        'status_description',
        'description',
    ];

}
