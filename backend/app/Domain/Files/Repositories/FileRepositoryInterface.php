<?php

namespace App\Domain\Files\Repositories;

use App\Domain\Files\Entities\File as FileEntity;

interface FileRepositoryInterface
{
    public function findById(int $id): ?FileEntity;

    public function findByUuid(string $uuid): ?FileEntity;

    public function findByIdAndOwner(int $id, string $ownerUuid): ?FileEntity;

    public function findByUuidAndOwner(string $uuid, string $ownerUuid): ?FileEntity;

    public function findByOriginalNameAndOwner(string $originalName, string $ownerUuid): ?FileEntity;

    public function findByOwner(string $ownerUuid, int $limit = 15, int $offset = 0): array;

    public function save(FileEntity $file): void;

    public function delete(FileEntity $file): void;

    public function getTotalByOwner(string $ownerUuid): int;

    public function getAllPaginated(int $limit = 15, int $offset = 0): array;

    public function getTotalCount(): int;
}
