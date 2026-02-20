<?php

namespace App\Domain\Files\ValueObjects;

final class FilePath
{
    private string $value;

    public function __construct(string $path)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('File path cannot be empty');
        }

        $this->value = $path;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function exists(): bool
    {
        return file_exists($this->value);
    }

    public function delete(): bool
    {
        if ($this->exists()) {
            return unlink($this->value);
        }
        return false;
    }
}
