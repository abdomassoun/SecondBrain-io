<?php

use App\Application\Files\Commands\DeleteFileCommand;
use App\Application\Files\Handlers\DeleteFileHandler;
use App\Application\Files\Services\FileActivityLogService;
use App\Domain\Files\Entities\File as FileEntity;
use App\Domain\Files\Repositories\FileRepositoryInterface;

test('delete file handler successfully deletes file', function () {
    // Create mock repository
    $repository = Mockery::mock(FileRepositoryInterface::class);
    $activityLogService = Mockery::mock(FileActivityLogService::class);
    
    $fileEntity = new FileEntity(
        id: 1,
        uuid: 'file-uuid-123',
        name: 'test.pdf',
        originalName: 'Test Document.pdf',
        size: 1024,
        mimeType: 'application/pdf',
        extension: 'pdf',
        path: '/tmp/test.pdf',
        ownerUuid: 'test-uuid-123',
        uploadDate: new \DateTime(),
    );

    // Set expectations
    $repository->shouldReceive('findByUuidAndOwner')
        ->once()
        ->with('file-uuid-123', 'test-uuid-123')
        ->andReturn($fileEntity);

    $activityLogService->shouldReceive('logActivity')
        ->once()
        ->with(
            'file-uuid-123',  // fileUuid
            'delete',         // action
            null,             // userId
            'test-uuid-123'   // ownerUuid
        );

    $repository->shouldReceive('delete')
        ->once()
        ->with($fileEntity);

    // Create handler
    $handler = new DeleteFileHandler($repository, $activityLogService);

    // Create command
    $command = new DeleteFileCommand(
        fileUuid: 'file-uuid-123',
        ownerUuid: 'test-uuid-123'
    );

    // Execute - should not throw exception
    $handler->handle($command);

    expect(true)->toBeTrue();
});

test('delete file handler throws exception when file not found', function () {
    $repository = Mockery::mock(FileRepositoryInterface::class);
    $activityLogService = Mockery::mock(FileActivityLogService::class);

    // File not found
    $repository->shouldReceive('findByUuidAndOwner')
        ->once()
        ->with('file-uuid-999', 'test-uuid-123')
        ->andReturn(null);

    $handler = new DeleteFileHandler($repository, $activityLogService);

    $command = new DeleteFileCommand(
        fileUuid: 'file-uuid-999',
        ownerUuid: 'test-uuid-123'
    );

    // Should throw exception
    $handler->handle($command);
})->throws(\Exception::class, 'File not found or you do not have permission to delete it');

test('delete file handler throws exception when user does not own file', function () {
    $repository = Mockery::mock(FileRepositoryInterface::class);
    $activityLogService = Mockery::mock(FileActivityLogService::class);

    // File belongs to different user
    $repository->shouldReceive('findByUuidAndOwner')
        ->once()
        ->with('file-uuid-123', 'wrong-uuid')
        ->andReturn(null);

    $handler = new DeleteFileHandler($repository, $activityLogService);

    $command = new DeleteFileCommand(
        fileUuid: 'file-uuid-123',
        ownerUuid: 'wrong-uuid'
    );

    $handler->handle($command);
})->throws(\Exception::class, 'File not found or you do not have permission to delete it');
