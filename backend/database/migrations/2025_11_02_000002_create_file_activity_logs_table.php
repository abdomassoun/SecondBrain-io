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
        Schema::create('file_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('file_uuid');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action', 50)->comment('upload, download, delete');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at');

            // Foreign key to files table
            $table->foreign('file_uuid')->references('uuid')->on('files')->onDelete('cascade');

            // Indexes
            $table->index('file_uuid');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_activity_logs');
    }
};
