<?php

namespace App\Application\Files\Services;

use App\Infrastructure\Persistence\Eloquent\Models\FileActivityLog;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\Auth;

class FileActivityLogService
{
    public function logActivity(
        string $fileUuid,
        string $action,
        ?int $userId = null,
        ?string $ownerUuid = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        // If userId is not provided, try to get it from ownerUuid or Auth
        if ($userId === null) {
            if ($ownerUuid !== null) {
                $userId = User::where('uuid', $ownerUuid)->value('id');
            } else {
                $userId = Auth::id() ?? request()->user()?->id;
            }
        }

        FileActivityLog::create([
            'file_uuid' => $fileUuid,
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }

    public function getFileActivityLogs(string $fileUuid, int $limit = 50): array
    {
        return FileActivityLog::where('file_uuid', $fileUuid)
            ->with('user:id,email')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getUserActivityLogs(int $userId, int $limit = 50): array
    {
        return FileActivityLog::where('user_id', $userId)
            ->with('file:id,original_name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
