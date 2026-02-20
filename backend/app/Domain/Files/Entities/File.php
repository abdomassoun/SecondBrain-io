<?php

namespace App\Domain\Files\Entities;

use App\Domain\Shared\Entity;

final class File extends Entity
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public string $name,
        public string $originalName,
        public int $size,
        public ?string $mimeType,
        public ?string $extension,
        public string $path,
        public string $ownerUuid,
        public \DateTime $uploadDate,
    ) {}

    public function __get($property)
    {
        return $this->$property;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getOwnerUuid(): string
    {
        return $this->ownerUuid;
    }

    public function getUploadDate(): \DateTime
    {
        return $this->uploadDate;
    }

    public function isOwnedBy(string $userUuid): bool
    {
        return $this->ownerUuid === $userUuid;
    }

    public function snapshot(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'original_name' => $this->originalName,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'extension' => $this->extension,
            'path' => $this->path,
            'owner_uuid' => $this->ownerUuid,
            'upload_date' => $this->uploadDate->format('Y-m-d H:i:s'),
        ];
    }
}
