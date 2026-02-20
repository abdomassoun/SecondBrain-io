<?php

namespace App\Application\Files\Commands;

class UploadChunkCommand
{
    public function __construct(
        public string $uploadId,
        public int $chunkIndex,
        public int $totalChunks,
        public string $originalName,
        public int $totalSize,
        public string $chunkData,
        public ?string $mimeType,
        public string $ownerUuid,
    ) {}
}
