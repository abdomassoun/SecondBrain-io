<?php

namespace App\Application\Users\Commands;

final class ForgotPasswordCommand
{
    public function __construct(
        public readonly string $email
    ) {}
}
