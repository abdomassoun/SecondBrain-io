<?php

namespace App\Application\Files\Services;

use App\Application\Files\DTOs\FileDTO;
use App\Application\Files\Queries\GetFileByIdQuery;
use App\Application\Files\Queries\GetFilesQuery;
use App\Infrastructure\Persistence\Eloquent\Models\File;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class FileQueryService
{
    public function __construct(private ?User $user = null) {}

    public function search(GetFilesQuery $query): LengthAwarePaginator
    {
        $queryBuilder = File::query();

        // Filter by owner if specified
        if ($query->ownerUuid) {
            $queryBuilder->where('owner_uuid', $query->ownerUuid);
        }

        // Filter by mime type if specified
        if ($query->mimeType) {
            $queryBuilder->where('mime_type', 'like', $query->mimeType . '%');
        }

        // Order by upload date desc
        $queryBuilder->orderBy('upload_date', 'desc');

        // Get total count before pagination
        $total = $queryBuilder->count();

        // Apply pagination
        $files = $queryBuilder
            ->limit($query->limit)
            ->offset($query->offset)
            ->get()
            ->map(fn($file) => FileDTO::fromModel($file));

        return new LengthAwarePaginator(
            items: $files,
            total: $total,
            perPage: $query->limit,
            currentPage: ($query->offset / $query->limit) + 1,
            options: ['path' => request()->url()]
        );
    }

    public function getFileById(GetFileByIdQuery $query): FileDTO
    {
        $queryBuilder = File::where('uuid', $query->fileUuid);

        // If owner UUID is provided, filter by it (for access control)
        if ($query->ownerUuid) {
            $queryBuilder->where('owner_uuid', $query->ownerUuid);
        }

        $file = $queryBuilder->firstOrFail();

        return FileDTO::fromModel($file);
    }

    public function getUserFiles(string $userUuid, int $limit = 15, int $offset = 0): LengthAwarePaginator
    {
        $query = new GetFilesQuery(
            ownerUuid: $userUuid,
            limit: $limit,
            offset: $offset
        );

        return $this->search($query);
    }
}
