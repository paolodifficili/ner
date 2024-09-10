<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coda', function (Blueprint $table) {
            $table->id();
            $table->timestamps();


            $table->string('uuid');
            $table->string('uuid_internal')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->string('file')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('email')->nullable();
            $table->string('description')->nullable();
            $table->string('root_folder')->nullable();
            $table->string('service_url')->nullable();
            $table->string('status_description')->nullable();

            $table->timestamp('last_run_at')->nullable();
            
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coda');
    }
};
