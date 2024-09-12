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
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
     
            $table->string('uuid')->nullable();
            $table->string('engine')->nullable();
            $table->string('engine_version')->nullable();
            $table->string('api')->nullable();
            $table->string('api_status')->nullable();
            $table->string('api_config')->nullable();
            $table->string('api_service')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('status_description')->nullable();
            $table->string('description')->nullable();
            

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