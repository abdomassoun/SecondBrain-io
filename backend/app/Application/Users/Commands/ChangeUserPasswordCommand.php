<?php

namespace App\Application\Users\Commands;

final class ChangeUserPasswordCommand
{
    public function __construct(
        public readonly string $userId,
        public readonly string $currentPassword,
        public readonly string $newPassword
    ) {}
}
