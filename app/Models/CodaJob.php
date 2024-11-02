<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodaJob extends Model
{
    use HasFactory;

    protected $table = 'codajob';
    protected $primaryKey = 'id';

    protected $fillable = [ 
        'job_uuid',
        'uuid_internal',
        'batch_uuid',
        'file',
        'info',
        'type',
        'engine',
        'engine_version',
        'status',
        'status_description',
        'description',
        'last_run_at', 
        'root_folder',
        'api_url',
        'status_url',
        'options',
        'data_in',
        'data_out',
        'email',
    ];

}
