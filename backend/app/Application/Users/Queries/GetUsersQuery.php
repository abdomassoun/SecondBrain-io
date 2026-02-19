<?php

namespace App\Application\Users\Queries;

final class GetUsersQuery
{
    public function __construct(
        public readonly ?string $email = null,
        public readonly int $limit = 15,
        public readonly int $offset = 0
    ) {}
}
