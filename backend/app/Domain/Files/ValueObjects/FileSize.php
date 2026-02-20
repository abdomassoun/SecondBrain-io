<?php

namespace App\Domain\Files\ValueObjects;

final class FileSize
{
    private int $bytes;

    public function __construct(int $bytes)
    {
        if ($bytes < 0) {
            throw new \InvalidArgumentException('File size cannot be negative');
        }

        $this->bytes = $bytes;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function getKilobytes(): float
    {
        return $this->bytes / 1024;
    }

    public function getMegabytes(): float
    {
        return $this->bytes / (1024 * 1024);
    }

    public function getGigabytes(): float
    {
        return $this->bytes / (1024 * 1024 * 1024);
    }

    public function isLargerThan(FileSize $other): bool
    {
        return $this->bytes > $other->bytes;
    }

    public function __toString(): string
    {
        return $this->formatHumanReadable();
    }

    public function formatHumanReadable(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->bytes;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
