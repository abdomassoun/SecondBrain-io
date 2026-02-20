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
        Schema::create('file_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('upload_id')->unique()->comment('Unique identifier for chunked upload session');
            $table->string('original_name')->comment('Original filename');
            $table->unsignedBigInteger('total_size')->comment('Total file size');
            $table->unsignedInteger('total_chunks')->comment('Total number of chunks');
            $table->unsignedInteger('uploaded_chunks')->default(0)->comment('Number of chunks uploaded');
            $table->string('mime_type', 100)->nullable();
            $table->uuid('owner_uuid');
            $table->json('chunk_paths')->nullable()->comment('Paths to uploaded chunks');
            $table->timestamp('expires_at')->comment('Expiration time for incomplete uploads');
            $table->timestamps();

            // Foreign key
            $table->foreign('owner_uuid')->references('uuid')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('upload_id');
            $table->index('owner_uuid');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_chunks');
    }
};
