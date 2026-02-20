<?php

namespace App\Application\Files\Queries;

class GetFileByIdQuery
{
    public function __construct(
        public string $fileUuid,
        public ?string $ownerUuid = null,
    ) {}
}
