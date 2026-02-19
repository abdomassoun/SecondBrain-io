<?php

namespace App\Domain\Shared;

use Illuminate\Support\Str;

abstract class Entity
{
    public ?string $id;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? (string) Str::uuid();
    }

    public function __get(string $property)
    {
        return $this->$property;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}

