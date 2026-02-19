<?php

namespace App\Application\Users\Commands;

final class DeleteUserCommand
{
    public function __construct(
        public string $userId
    ) {}
}
