<?php

use App\Application\Users\Commands\CreateUserCommand;

it('can create command with required fields', function () {
    $command = new CreateUserCommand(
        email: 'user@example.com',
        password: 'hashed_password_123',
    );

    expect($command->email)->toBe('user@example.com')
        ->and($command->password)->toBe('hashed_password_123');
});

it('command fields are readonly', function () {
    $command = new CreateUserCommand(
        email: 'user@example.com',
        password: 'password',
    );

    $errorMessage = null;

    try {
        $command->email = 'modified@example.com';
    } catch (\Error $e) {
        $errorMessage = $e->getMessage();
    }

    expect($errorMessage)->toContain('Cannot modify readonly property');
});
