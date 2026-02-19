<?php

namespace App\Domain\Users\ValueObjects;

use InvalidArgumentException;

class Email
{
    public string $value;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$email}");
        }

        $this->value = strtolower($email); // normalize
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}

