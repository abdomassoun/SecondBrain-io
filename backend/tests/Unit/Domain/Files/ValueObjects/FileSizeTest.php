<?php

use App\Domain\Files\ValueObjects\FileSize;

test('file size can be created with valid bytes', function () {
    $size = new FileSize(1024);
    expect($size->getBytes())->toBe(1024);
});

test('file size throws exception for negative bytes', function () {
    new FileSize(-100);
})->throws(\InvalidArgumentException::class, 'File size cannot be negative');

test('file size converts to kilobytes correctly', function () {
    $size = new FileSize(2048);
    expect($size->getKilobytes())->toBe(2.0);
});

test('file size converts to megabytes correctly', function () {
    $size = new FileSize(5242880); // 5 MB
    expect($size->getMegabytes())->toBe(5.0);
});

test('file size converts to gigabytes correctly', function () {
    $size = new FileSize(2147483648); // 2 GB
    expect($size->getGigabytes())->toBe(2.0);
});

test('file size comparison works correctly', function () {
    $size1 = new FileSize(1024);
    $size2 = new FileSize(2048);

    expect($size2->isLargerThan($size1))->toBeTrue();
    expect($size1->isLargerThan($size2))->toBeFalse();
});

test('file size formats human readable correctly', function () {
    expect((new FileSize(100))->formatHumanReadable())->toBe('100 B');
    expect((new FileSize(1024))->formatHumanReadable())->toBe('1 KB');
    expect((new FileSize(1048576))->formatHumanReadable())->toBe('1 MB');
    expect((new FileSize(1073741824))->formatHumanReadable())->toBe('1 GB');
});
