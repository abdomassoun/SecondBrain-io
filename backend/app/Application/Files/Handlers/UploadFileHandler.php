<?php

namespace App\Application\Files\Handlers;

use App\Application\Files\Commands\UploadFileCommand;
use App\Application\Files\Services\FileUploadService;

class UploadFileHandler
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    public function handle(UploadFileCommand $command)
    {
        return $this->fileUploadService->uploadFile(
            file: $command->file,
            ownerUuid: $command->ownerUuid
        );
    }
}
