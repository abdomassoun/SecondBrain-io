<?php

namespace App\Application\Files\DTOs;

use App\Infrastructure\Persistence\Eloquent\Models\FileChunk;

final class ChunkUploadDTO
{
    public function __construct(
        public readonly string $uploadId,
        public readonly string $originalName,
        public readonly int $totalSize,
        public readonly int $totalChunks,
        public readonly int $uploadedChunks,
        public readonly bool $isComplete,
        public readonly \DateTime $expiresAt,
    ) {}

    public static function fromModel(FileChunk $model): self
    {
        return new self(
            uploadId: $model->upload_id,
            originalName: $model->original_name,
            totalSize: $model->total_size,
            totalChunks: $model->total_chunks,
            uploadedChunks: $model->uploaded_chunks,
            isComplete: $model->isComplete(),
            expiresAt: $model->expires_at,
        );
    }
}
