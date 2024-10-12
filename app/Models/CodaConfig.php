<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodaConfig extends Model
{
    use HasFactory;

    protected $table = 'codaconfig';
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
        'options',
    ];

}
