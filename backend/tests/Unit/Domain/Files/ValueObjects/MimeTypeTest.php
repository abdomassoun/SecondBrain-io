<?php

use App\Domain\Files\ValueObjects\MimeType;

test('mime type can be created with valid type', function () {
    $mimeType = new MimeType('application/pdf');
    expect($mimeType->getValue())->toBe('application/pdf');
});

test('mime type is case insensitive', function () {
    $mimeType = new MimeType('APPLICATION/PDF');
    expect($mimeType->getValue())->toBe('application/pdf');
});

test('mime type validates allowed types correctly', function () {
    $allowedType = new MimeType('application/pdf');
    expect($allowedType->isAllowed())->toBeTrue();

    $disallowedType = new MimeType('application/x-executable');
    expect($disallowedType->isAllowed())->toBeFalse();
});

test('mime type correctly identifies images', function () {
    expect((new MimeType('image/jpeg'))->isImage())->toBeTrue();
    expect((new MimeType('image/png'))->isImage())->toBeTrue();
    expect((new MimeType('application/pdf'))->isImage())->toBeFalse();
});

test('mime type correctly identifies documents', function () {
    expect((new MimeType('application/pdf'))->isDocument())->toBeTrue();
    expect((new MimeType('text/plain'))->isDocument())->toBeTrue();
    expect((new MimeType('image/jpeg'))->isDocument())->toBeFalse();
});

test('mime type correctly identifies videos', function () {
    expect((new MimeType('video/mp4'))->isVideo())->toBeTrue();
    expect((new MimeType('video/mpeg'))->isVideo())->toBeTrue();
    expect((new MimeType('image/jpeg'))->isVideo())->toBeFalse();
});

test('mime type correctly identifies audio', function () {
    expect((new MimeType('audio/mpeg'))->isAudio())->toBeTrue();
    expect((new MimeType('audio/wav'))->isAudio())->toBeTrue();
    expect((new MimeType('video/mp4'))->isAudio())->toBeFalse();
});

test('mime type can retrieve all allowed types', function () {
    $allowedTypes = MimeType::getAllowedTypes();
    
    expect($allowedTypes)->toBeArray();
    expect(count($allowedTypes))->toBeGreaterThan(0);
    expect($allowedTypes)->toContain('application/pdf');
    expect($allowedTypes)->toContain('image/jpeg');
});
