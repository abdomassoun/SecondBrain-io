<?php

namespace App\Application\Files\Commands;

class DeleteFileCommand
{
    public function __construct(
        public string $fileUuid,
        public string $ownerUuid,
    ) {}
}
