<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodaBatch extends Model
{
    use HasFactory;

    protected $table = 'codabatch';
    protected $primaryKey = 'id';

    protected $fillable = [ 
        'batch_uuid',
        'batch_description',
        'batch_options',
        'batch_action',
        'file',
        'info',
        'status',
        'last_run_at',
    ];

    /*
            $table->string('batch_uuid')->nullable();
            $table->string('batch_description')->nullable();
            $table->json('batch_options')->nullable();
            $table->string('file')->nullable();
            $table->string('info')->nullable();
            $table->string('status')->nullable();

            $table->timestamp('last_run_at')->nullable();

    */

}
