<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class FileChunk extends Model
{
    protected $fillable = [
        'upload_id',
        'original_name',
        'total_size',
        'total_chunks',
        'uploaded_chunks',
        'mime_type',
        'owner_uuid',
        'chunk_paths',
        'expires_at',
    ];

    protected $casts = [
        'total_size' => 'integer',
        'total_chunks' => 'integer',
        'uploaded_chunks' => 'integer',
        'chunk_paths' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_uuid', 'uuid');
    }

    public function isComplete(): bool
    {
        return $this->uploaded_chunks === $this->total_chunks;
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }
}
