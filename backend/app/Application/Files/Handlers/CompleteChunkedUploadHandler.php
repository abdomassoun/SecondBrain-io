<?php

namespace App\Application\Files\Handlers;

use App\Application\Files\Commands\CompleteChunkedUploadCommand;
use App\Application\Files\Services\FileUploadService;

class CompleteChunkedUploadHandler
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    public function handle(CompleteChunkedUploadCommand $command)
    {
        return $this->fileUploadService->completeChunkedUpload(
            uploadId: $command->uploadId,
            ownerUuid: $command->ownerUuid
        );
    }
}
