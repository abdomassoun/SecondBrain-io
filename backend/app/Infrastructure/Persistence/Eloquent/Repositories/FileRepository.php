<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Files\Entities\File as FileEntity;
use App\Domain\Files\Repositories\FileRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\File;
use Illuminate\Support\Facades\DB;

class FileRepository implements FileRepositoryInterface
{
    public function findById(int $id): ?FileEntity
    {
        $file = File::find($id);
        
        if (!$file) {
            return null;
        }

        return $file->toDomainEntity();
    }

    public function findByUuid(string $uuid): ?FileEntity
    {
        $file = File::where('uuid', $uuid)->first();
        
        if (!$file) {
            return null;
        }

        return $file->toDomainEntity();
    }

    public function findByIdAndOwner(int $id, string $ownerUuid): ?FileEntity
    {
        $file = File::where('id', $id)
            ->where('owner_uuid', $ownerUuid)
            ->first();

        if (!$file) {
            return null;
        }

        return $file->toDomainEntity();
    }

    public function findByUuidAndOwner(string $uuid, string $ownerUuid): ?FileEntity
    {
        $file = File::where('uuid', $uuid)
            ->where('owner_uuid', $ownerUuid)
            ->first();

        if (!$file) {
            return null;
        }

        return $file->toDomainEntity();
    }

    public function findByOwner(string $ownerUuid, int $limit = 15, int $offset = 0): array
    {
        return File::where('owner_uuid', $ownerUuid)
            ->orderBy('upload_date', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn($model) => $model->toDomainEntity())
            ->toArray();
    }

    public function save(FileEntity $file): void
    {
        DB::transaction(function () use ($file) {
            $model = new File();
            $model->name = $file->name;
            $model->original_name = $file->originalName;
            $model->size = $file->size;
            $model->mime_type = $file->mimeType;
            $model->extension = $file->extension;
            $model->path = $file->path;
            $model->owner_uuid = $file->ownerUuid;
            $model->upload_date = $file->uploadDate;
            $model->save();

            $file->id = $model->id;
            $file->uuid = $model->uuid;
        });
    }

    public function delete(FileEntity $file): void
    {
        File::where('uuid', $file->uuid)->delete();
    }

    public function getTotalByOwner(string $ownerUuid): int
    {
        return File::where('owner_uuid', $ownerUuid)->count();
    }

    public function getAllPaginated(int $limit = 15, int $offset = 0): array
    {
        return File::orderBy('upload_date', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn($model) => $model->toDomainEntity())
            ->toArray();
    }

    public function getTotalCount(): int
    {
        return File::count();
    }
}
