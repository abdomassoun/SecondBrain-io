<?php

use App\Domain\Users\Entities\User;
use App\Domain\Users\Entities\UserProfile;
use Domain\Shared\UserStatus\UserStatus;
use Domain\Shared\Language\Language;

beforeEach(function () {
    $this->user = new User(
        id: '1',
        uuid: 'user-uuid-123',
        email: 'john@example.com',
        password: password_hash('password123', PASSWORD_BCRYPT),
        companyUuid: 'company-uuid-123',
        brandUuid: 'brand-uuid-123',
        branchUuid: 'branch-uuid-123',
        directManagerUuid: null,
        emailVerifiedAt: null,
        lastLoginAt: null,
        status: UserStatus::ACTIVE,
        defaultLanguage: Language::EN,
        profile: new UserProfile(
            id: 1,
            userUuid: 'user-uuid-123',
            firstName: 'John',
            lastName: 'Doe',
            phone: '1234567890',
            jobTitle: 'Developer',
            gender: true,
            dateOfBirth: new DateTime('1990-01-01'),
            employmentStartDate: new DateTime('2020-01-01'),
            bio: 'A skilled developer.',
            address: '123 Main St, City, Country',
            socialLinks: ['linkedin' => 'https://linkedin.com/in/johndoe']
        )
    );
});

it('can verify password correctly', function () {
    expect($this->user->verifyPassword('password123'))->toBeTrue()
        ->and($this->user->verifyPassword('wrongpassword'))->toBeFalse();
});

it('can verify email', function () {
    expect($this->user->emailVerifiedAt)->toBeNull();

    $this->user->verifyEmail();

    expect($this->user->emailVerifiedAt)->toBeInstanceOf(DateTime::class);
});

it('can change password', function () {
    $this->user->changePassword(password_hash('newpassword123', PASSWORD_BCRYPT));

    expect($this->user->verifyPassword('newpassword123'))->toBeTrue()
        ->and($this->user->verifyPassword('password123'))->toBeFalse();
});

it('can activate user', function () {
    $this->user->deactivate();
    expect($this->user->isActive())->toBeFalse();

    $this->user->activate();
    expect($this->user->isActive())->toBeTrue()
        ->and($this->user->status)->toBe(UserStatus::ACTIVE);
});

it('can deactivate user', function () {
    expect($this->user->isActive())->toBeTrue();

    $this->user->deactivate();
    expect($this->user->isActive())->toBeFalse()
        ->and($this->user->status)->toBe(UserStatus::INACTIVE);
});

it('can suspend user', function () {
    $this->user->suspend();

    expect($this->user->isActive())->toBeFalse()
        ->and($this->user->status)->toBe(UserStatus::SUSPENDED);
});

it('returns correct id', function () {
    expect($this->user->getId())->toBe('1');
});

it('returns correct company uuid', function () {
    expect($this->user->getCompanyUuid())->toBe('company-uuid-123');
});

it('can set and get default language', function () {
    expect($this->user->getDefaultLanguage())->toBe(Language::EN);

    $this->user->setDefaultLanguage(Language::AR);
    expect($this->user->getDefaultLanguage())->toBe(Language::AR);
});

it('returns snapshot with essential data', function () {
    $snapshot = $this->user->snapshot();

    expect($snapshot)->toBeArray()
        ->and($snapshot['id'])->toBe('1')
        ->and($snapshot['email'])->toBe('john@example.com')
        ->and($snapshot)->toHaveKey('name');
});

it('can access properties via magic get', function () {
    expect($this->user->__get('companyUuid'))->toBe('company-uuid-123')
        ->and($this->user->__get('brandUuid'))->toBe('brand-uuid-123')
        ->and($this->user->__get('branchUuid'))->toBe('branch-uuid-123');
});

it('can be created with null optional fields', function () {
    $user = new User(
        id: null,
        uuid: null,
        email: 'jane@example.com',
        password: password_hash('secure123', PASSWORD_BCRYPT),
        companyUuid: null,
        brandUuid: null,
        branchUuid: null,
        directManagerUuid: null,
        emailVerifiedAt: null,
        lastLoginAt: null,
        status: UserStatus::INACTIVE,
        defaultLanguage: null,
        profile: new UserProfile(
            id: null,
            userUuid: null,
            firstName: null,
            lastName: null,
            phone: null,
            jobTitle: null,
            gender: null,
            dateOfBirth: null,
            employmentStartDate: null,
            bio: null,
            address: null,
            socialLinks: null
        )
    );

    expect($user->getId())->toBeNull()
        ->and($user->getCompanyUuid())->toBeNull()
        ->and($user->getDefaultLanguage())->toBeNull()
        ->and($user->status)->toBe(UserStatus::INACTIVE);
});

it('is not active when suspended', function () {
    $user = new User(
        id: '2',
        uuid: 'user-uuid-456',
        email: 'suspended@example.com',
        password: password_hash('pass123', PASSWORD_BCRYPT),
        companyUuid: 'company-uuid-123',
        brandUuid: null,
        branchUuid: null,
        directManagerUuid: null,
        emailVerifiedAt: null,
        lastLoginAt: null,
        status: UserStatus::SUSPENDED,
        defaultLanguage: Language::EN,
        profile: new UserProfile(
            id: null,
            userUuid: null,
            firstName: null,
            lastName: null,
            phone: null,
            jobTitle: null,
            gender: null,
            dateOfBirth: null,
            employmentStartDate: null,
            bio: null,
            address: null,
            socialLinks: null
        )
    );

    expect($user->isActive())->toBeFalse();
});

it('is not active when inactive', function () {
    $user = new User(
        id: '3',
        uuid: 'user-uuid-789',
        email: 'inactive@example.com',
        password: password_hash('pass123', PASSWORD_BCRYPT),
        companyUuid: 'company-uuid-123',
        brandUuid: null,
        branchUuid: null,
        directManagerUuid: null,
        emailVerifiedAt: null,
        lastLoginAt: null,
        status: UserStatus::INACTIVE,
        defaultLanguage: Language::EN,
        profile: new UserProfile(
            id: null,
            userUuid: null,
            firstName: null,
            lastName: null,
            phone: null,
            jobTitle: null,
            gender: null,
            dateOfBirth: null,
            employmentStartDate: null,
            bio: null,
            address: null,
            socialLinks: null
        )
    );

    expect($user->isActive())->toBeFalse();
});
