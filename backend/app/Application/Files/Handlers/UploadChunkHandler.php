<?php

namespace App\Application\Files\Handlers;

use App\Application\Files\Commands\UploadChunkCommand;
use App\Application\Files\Services\FileUploadService;

class UploadChunkHandler
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    public function handle(UploadChunkCommand $command)
    {
        return $this->fileUploadService->uploadChunk(
            uploadId: $command->uploadId,
            chunkIndex: $command->chunkIndex,
            totalChunks: $command->totalChunks,
            originalName: $command->originalName,
            totalSize: $command->totalSize,
            chunkData: $command->chunkData,
            mimeType: $command->mimeType,
            ownerUuid: $command->ownerUuid
        );
    }
}
