<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodaFile extends Model
{
    use HasFactory;

    protected $table = 'codafile';
    protected $primaryKey = 'id';

    protected $fillable = [ 
        'file_uuid',
        'file_name',
        'file_size',
        'file_path',
        'file_extension',
        'file_root_path',
        'info',
        'status',
        'last_run_at',
    ];

    /*
            
            $table->string('file_uuid')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_extension')->nullable();
            $table->string('file_root_path')->nullable();
            $table->string('info')->nullable();
            $table->string('status')->nullable();

            $table->timestamp('last_run_at')->nullable();

    */

}
