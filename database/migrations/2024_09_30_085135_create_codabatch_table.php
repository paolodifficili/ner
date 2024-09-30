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
        Schema::create('codabatch', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('batch_uuid')->nullable();
            $table->string('batch_description')->nullable();
            $table->string('batch_action')->nullable();
            $table->json('batch_options')->nullable();
            $table->string('file')->nullable();
            $table->string('info')->nullable();
            $table->string('status')->nullable();

            $table->timestamp('last_run_at')->nullable();



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codabatch');
    }
};
