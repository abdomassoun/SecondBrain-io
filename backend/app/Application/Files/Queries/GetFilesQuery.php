<?php

namespace App\Application\Files\Queries;

class GetFilesQuery
{
    public function __construct(
        public ?string $ownerUuid = null,
        public ?string $mimeType = null,
        public int $limit = 15,
        public int $offset = 0,
    ) {}
}
