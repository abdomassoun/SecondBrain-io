<?php

namespace App\Application\Files\Commands;

class CompleteChunkedUploadCommand
{
    public function __construct(
        public string $uploadId,
        public string $ownerUuid,
    ) {}
}
