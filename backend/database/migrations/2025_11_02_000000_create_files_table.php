<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Unique identifier for the file');
            $table->string('name')->comment('Stored filename');
            $table->string('original_name')->comment('Original filename from upload');
            $table->unsignedBigInteger('size')->comment('File size in bytes');
            $table->string('mime_type', 100)->nullable()->comment('File MIME type for type validation');
            $table->string('extension', 20)->nullable()->comment('File extension');
            $table->string('path', 500)->comment('Storage path or file location');
            $table->uuid('owner_uuid')->comment('Reference to user UUID for ownership');
            $table->timestamp('upload_date')->comment('Upload completion date');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key to users table
            $table->foreign('owner_uuid')->references('uuid')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index('owner_uuid');
            $table->index('upload_date');
            $table->index('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
