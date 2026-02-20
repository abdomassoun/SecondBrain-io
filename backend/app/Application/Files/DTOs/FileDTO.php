<?php

namespace App\Application\Files\DTOs;

use App\Domain\Files\Entities\File as FileEntity;
use App\Infrastructure\Persistence\Eloquent\Models\File;

final class FileDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $originalName,
        public readonly int $size,
        public readonly string $sizeFormatted,
        public readonly ?string $mimeType,
        public readonly ?string $extension,
        public readonly string $ownerUuid,
        public readonly \DateTime $uploadDate,
        public readonly \DateTime $createdAt,
        public readonly \DateTime $updatedAt,
    ) {}

    public static function fromModel(File $model): self
    {
        return new self(
            id: $model->id,
            uuid: $model->uuid,
            name: $model->name,
            originalName: $model->original_name,
            size: $model->size,
            sizeFormatted: self::formatFileSize($model->size),
            mimeType: $model->mime_type,
            extension: $model->extension,
            ownerUuid: $model->owner_uuid,
            uploadDate: $model->upload_date,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public static function fromEntity(FileEntity $entity): self
    {
        $model = File::where('uuid', $entity->uuid)->first();
        
        if (!$model) {
            throw new \RuntimeException('File not found');
        }
        
        return self::fromModel($model);
    }

    private static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
