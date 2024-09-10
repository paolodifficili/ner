<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coda extends Model
{
    use HasFactory;

    protected $table = 'coda';
    protected $primaryKey = 'id';

    protected $fillable = [ 
        'uuid',
        'uuid_internal',
        'batch_uuid',
        'file',
        'type',
        'status',
        'status_description',
        'description',
        'last_run_at', 
        'root_folder',
        'service_url',
        'email',
    ];

}
