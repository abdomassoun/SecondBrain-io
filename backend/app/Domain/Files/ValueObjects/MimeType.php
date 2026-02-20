<?php

namespace App\Domain\Files\ValueObjects;

final class MimeType
{
    private string $value;

    // Allowed mime types for validation
    private const ALLOWED_TYPES = [
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        // Archives
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        // Video
        'video/mp4',
        'video/mpeg',
        'video/quicktime',
        'video/x-msvideo',
        // Audio
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
    ];

    public function __construct(string $mimeType)
    {
        $this->value = strtolower($mimeType);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function isAllowed(): bool
    {
        return in_array($this->value, self::ALLOWED_TYPES);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->value, 'image/');
    }

    public function isDocument(): bool
    {
        return str_starts_with($this->value, 'application/') || 
               str_starts_with($this->value, 'text/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->value, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->value, 'audio/');
    }

    public static function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }
}
