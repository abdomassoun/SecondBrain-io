<?php

use App\Domain\Files\Entities\File;

test('file entity can be created with valid data', function () {
    $file = new File(
        id: 1,
        uuid: null,
        name: 'stored-file.pdf',
        originalName: 'My Document.pdf',
        size: 2048,
        mimeType: 'application/pdf',
        extension: 'pdf',
        path: '/storage/files/stored-file.pdf',
        ownerUuid: 'user-uuid-123',
        uploadDate: new \DateTime('2024-01-01 12:00:00'),
    );

    expect($file->getId())->toBe(1);
    expect($file->getName())->toBe('stored-file.pdf');
    expect($file->getOriginalName())->toBe('My Document.pdf');
    expect($file->getSize())->toBe(2048);
    expect($file->getMimeType())->toBe('application/pdf');
    expect($file->getExtension())->toBe('pdf');
    expect($file->getPath())->toBe('/storage/files/stored-file.pdf');
    expect($file->getOwnerUuid())->toBe('user-uuid-123');
});

test('file entity correctly identifies ownership', function () {
    $file = new File(
        id: 1,
        uuid: null,
        name: 'test.pdf',
        originalName: 'Test.pdf',
        size: 1024,
        mimeType: 'application/pdf',
        extension: 'pdf',
        path: '/storage/test.pdf',
        ownerUuid: 'correct-uuid',
        uploadDate: new \DateTime(),
    );

    expect($file->isOwnedBy('correct-uuid'))->toBeTrue();
    expect($file->isOwnedBy('wrong-uuid'))->toBeFalse();
});

test('file entity snapshot returns correct data', function () {
    $uploadDate = new \DateTime('2024-01-01 12:00:00');
    
    $file = new File(
        id: 1,
        uuid: null,
        name: 'test.pdf',
        originalName: 'Test Document.pdf',
        size: 1024,
        mimeType: 'application/pdf',
        extension: 'pdf',
        path: '/storage/test.pdf',
        ownerUuid: 'user-123',
        uploadDate: $uploadDate,
    );

    $snapshot = $file->snapshot();

    expect($snapshot)->toBeArray();
    expect($snapshot['id'])->toBe(1);
    expect($snapshot['name'])->toBe('test.pdf');
    expect($snapshot['original_name'])->toBe('Test Document.pdf');
    expect($snapshot['size'])->toBe(1024);
    expect($snapshot['mime_type'])->toBe('application/pdf');
    expect($snapshot['extension'])->toBe('pdf');
    expect($snapshot['path'])->toBe('/storage/test.pdf');
    expect($snapshot['owner_uuid'])->toBe('user-123');
    expect($snapshot['upload_date'])->toBe('2024-01-01 12:00:00');
});
