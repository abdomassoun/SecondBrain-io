<?php

namespace App\Application\Files\Commands;

use Illuminate\Http\UploadedFile;

class UploadFileCommand
{
    public function __construct(
        public UploadedFile $file,
        public string $ownerUuid,
    ) {}
}
