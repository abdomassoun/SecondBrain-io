<?php

namespace App\Application\Users\Commands;

final class CreateUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
