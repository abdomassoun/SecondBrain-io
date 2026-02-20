<?php

namespace App\Application\Files\Handlers;

use App\Application\Files\Commands\DeleteFileCommand;
use App\Application\Files\Services\FileActivityLogService;
use App\Domain\Files\Repositories\FileRepositoryInterface;
use App\Domain\Files\ValueObjects\FilePath;

class DeleteFileHandler
{
    public function __construct(
        private FileRepositoryInterface $fileRepository,
        private FileActivityLogService $activityLogService
    ) {}

    public function handle(DeleteFileCommand $command): void
    {
        $file = $this->fileRepository->findByUuidAndOwner(
            $command->fileUuid,
            $command->ownerUuid
        );

        if (!$file) {
            throw new \Exception('File not found or you do not have permission to delete it');
        }

        // Log the deletion activity
        $this->activityLogService->logActivity(
            fileUuid: $file->uuid,
            action: 'delete',
            ownerUuid: $command->ownerUuid
        );

        // Delete physical file
        $filePath = new FilePath($file->path);
        $filePath->delete();

        // Delete from database
        $this->fileRepository->delete($file);
    }
}
