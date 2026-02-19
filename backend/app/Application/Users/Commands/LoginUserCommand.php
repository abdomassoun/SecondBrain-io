<?php

namespace App\Application\Users\Commands;

class LoginUserCommand
{
    public function __construct(
        public string $email,
        public string $password
    ) {}
}

