<?php

namespace App\Application\Users\Commands;

final class ResetPasswordCommand 
{
    public function __construct(
        public readonly string $email,
        public readonly string $otp,
        public readonly string $newPassword
    ) {}
}
