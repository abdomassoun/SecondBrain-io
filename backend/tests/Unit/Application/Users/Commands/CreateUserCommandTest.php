<?php

use App\Application\Users\Commands\CreateUserCommand;

it('can create command with all fields', function () {
    $command = new CreateUserCommand(
        email: 'user@example.com',
        password: 'hashed_password_123',
        roleName: 'Employee',
        companyUuid: 'company-uuid-123',
        brandUuid: 'brand-uuid-123',
        branchUuid: 'branch-uuid-123',
        directManagerUuid: 'manager-uuid-456',
        statusId: 1,
        defaultLanguage: 'en'
    );

    expect($command->email)->toBe('user@example.com')
        ->and($command->password)->toBe('hashed_password_123')
        ->and($command->roleName)->toBe('Employee')
        ->and($command->companyUuid)->toBe('company-uuid-123')
        ->and($command->brandUuid)->toBe('brand-uuid-123')
        ->and($command->branchUuid)->toBe('branch-uuid-123')
        ->and($command->directManagerUuid)->toBe('manager-uuid-456')
        ->and($command->statusId)->toBe(1)
        ->and($command->defaultLanguage)->toBe('en');
});

it('can create command with minimal fields', function () {
    $command = new CreateUserCommand(
        email: 'user@example.com',
        password: 'hashed_password_123',
        roleName: 'Employee'
    );

    expect($command->email)->toBe('user@example.com')
        ->and($command->password)->toBe('hashed_password_123')
        ->and($command->roleName)->toBe('Employee')
        ->and($command->companyUuid)->toBeNull()
        ->and($command->brandUuid)->toBeNull()
        ->and($command->branchUuid)->toBeNull()
        ->and($command->directManagerUuid)->toBeNull()
        ->and($command->statusId)->toBe(1) // Default status
        ->and($command->defaultLanguage)->toBeNull();
});

it('command fields are readonly', function () {
    $command = new CreateUserCommand(
        email: 'user@example.com',
        password: 'password',
        roleName: 'Employee'
    );

    $errorMessage = null;

    try {
        $command->email = 'modified@example.com';
    } catch (\Error $e) {
        $errorMessage = $e->getMessage();
    }

    expect($errorMessage)->toContain('Cannot modify readonly property');
});
