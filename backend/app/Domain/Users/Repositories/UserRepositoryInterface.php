<?php

namespace App\Domain\Users\Repositories;

use App\Domain\Users\Entities\User as UserEntity;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?UserEntity;

    public function findById(string $id): ?UserEntity;

    public function findByUuid(string $uuid): ?UserEntity;

    public function save(UserEntity $user): void;

    public function delete(UserEntity $user): void;

    public function update(UserEntity $user): void;

    public function changePassword(string $userId, string $hashedPassword): void;

    public function findAll(): array;
}
