<?php

use App\Domain\Users\Entities\User;

beforeEach(function () {
    $this->user = new User(
        id: 1,
        uuid: 'user-uuid-123',
        email: 'john@example.com',
        password: password_hash('password123', PASSWORD_BCRYPT),
    );
});

it('can verify password correctly', function () {
    expect($this->user->verifyPassword('password123'))->toBeTrue()
        ->and($this->user->verifyPassword('wrongpassword'))->toBeFalse();
});

it('can change password', function () {
    $this->user->changePassword(password_hash('newpassword123', PASSWORD_BCRYPT));

    expect($this->user->verifyPassword('newpassword123'))->toBeTrue()
        ->and($this->user->verifyPassword('password123'))->toBeFalse();
});

it('returns snapshot with essential data', function () {
    $snapshot = $this->user->snapshot();

    expect($snapshot)->toBeArray()
        ->and($snapshot['id'])->toBe(1)
        ->and($snapshot['uuid'])->toBe('user-uuid-123')
        ->and($snapshot['email'])->toBe('john@example.com');
});

it('can access email via magic get', function () {
    expect($this->user->__get('email'))->toBe('john@example.com');
});

it('can be created with null optional fields', function () {
    $user = new User(
        id: null,
        uuid: null,
        email: 'jane@example.com',
        password: password_hash('secure123', PASSWORD_BCRYPT),
    );

    expect($user->id)->toBeNull()
        ->and($user->uuid)->toBeNull()
        ->and($user->getEmail())->toBe('jane@example.com');
});
