<?php

namespace App\Domain\Shared;

use Illuminate\Support\Str;

abstract class Entity
{
    public ?int $id;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function __get(string $property)
    {
        return $this->$property;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

