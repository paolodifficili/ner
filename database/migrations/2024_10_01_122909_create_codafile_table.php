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
        Schema::create('codafile', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('file_uuid')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_extension')->nullable();
            $table->string('file_root_path')->nullable();
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
        Schema::dropIfExists('codafile');
    }
};
