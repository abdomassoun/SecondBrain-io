<?php

namespace App\Domain\Users\Entities;

use App\Domain\Shared\Entity;

final class User extends Entity
{
    public function __construct(
        public ?string $id,
        public ?string $uuid,
        private string $email,
        private string $password,
    ) {}

    public function __get($property)
    {
        return $this->$property;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function changePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }

    public function snapshot(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'email' => $this->email,
        ];
    }

}

