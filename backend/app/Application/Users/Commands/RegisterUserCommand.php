<?php

namespace App\Application\Users\Commands;

final class RegisterUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
