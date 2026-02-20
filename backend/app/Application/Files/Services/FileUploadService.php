<?php

namespace App\Application\Files\Services;

use App\Domain\Files\Entities\File as FileEntity;
use App\Domain\Files\Repositories\FileRepositoryInterface;
use App\Domain\Files\ValueObjects\MimeType;
use App\Infrastructure\Persistence\Eloquent\Models\FileChunk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function __construct(
        private FileRepositoryInterface $fileRepository,
        private FileActivityLogService $activityLogService
    ) {}

    /**
     * Upload a single file (non-chunked)
     */
    public function uploadFile(UploadedFile $file, string $ownerUuid): FileEntity
    {
        // Validate mime type
        $mimeType = new MimeType($file->getMimeType());
        if (!$mimeType->isAllowed()) {
            throw new \Exception('File type not allowed. Allowed types: ' . implode(', ', MimeType::getAllowedTypes()));
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = 'files/' . date('Y/m/d') . '/' . $filename;

        // Store the file
        Storage::put($path, file_get_contents($file->getRealPath()));

        // Create file entity
        $fileEntity = new FileEntity(
            id: null,
            uuid: null,   
            name: $filename,
            originalName: $file->getClientOriginalName(),
            size: $file->getSize(),
            mimeType: $file->getMimeType(),
            extension: $extension,
            path: Storage::path($path),
            ownerUuid: $ownerUuid,
            uploadDate: new \DateTime(),
        );

        // Save to database
        $this->fileRepository->save($fileEntity);

        // Log activity
        $this->activityLogService->logActivity(
            fileUuid: $fileEntity->uuid,
            action: 'upload',
            ownerUuid: $ownerUuid
        );

        return $fileEntity;
    }

    /**
     * Upload a chunk of a file
     */
    public function uploadChunk(
        string $uploadId,
        int $chunkIndex,
        int $totalChunks,
        string $originalName,
        int $totalSize,
        string $chunkData,
        ?string $mimeType,
        string $ownerUuid
    ): array {
        // Find or create chunk upload session
        $chunkUpload = FileChunk::firstOrCreate(
            ['upload_id' => $uploadId],
            [
                'original_name' => $originalName,
                'total_size' => $totalSize,
                'total_chunks' => $totalChunks,
                'uploaded_chunks' => 0,
                'mime_type' => $mimeType,
                'owner_uuid' => $ownerUuid,
                'chunk_paths' => [],
                'expires_at' => now()->addHours(24), // 24 hours to complete upload
            ]
        );

        // Check if expired
        if ($chunkUpload->isExpired()) {
            $this->cleanupChunks($chunkUpload);
            throw new \Exception('Upload session has expired. Please start a new upload.');
        }

        // Store the chunk
        $chunkPath = 'chunks/' . $uploadId . '/chunk_' . $chunkIndex;
        Storage::put($chunkPath, base64_decode($chunkData));

        // Update chunk paths
        $chunkPaths = $chunkUpload->chunk_paths ?? [];
        $chunkPaths[$chunkIndex] = Storage::path($chunkPath);
        $chunkUpload->chunk_paths = $chunkPaths;
        $chunkUpload->uploaded_chunks = count($chunkPaths);
        $chunkUpload->save();

        return [
            'upload_id' => $uploadId,
            'uploaded_chunks' => $chunkUpload->uploaded_chunks,
            'total_chunks' => $totalChunks,
            'is_complete' => $chunkUpload->isComplete(),
        ];
    }

    /**
     * Complete chunked upload by merging all chunks
     */
    public function completeChunkedUpload(string $uploadId, string $ownerUuid): FileEntity
    {
        $chunkUpload = FileChunk::where('upload_id', $uploadId)
            ->where('owner_uuid', $ownerUuid)
            ->firstOrFail();

        if (!$chunkUpload->isComplete()) {
            throw new \Exception('Not all chunks have been uploaded. Uploaded: ' . $chunkUpload->uploaded_chunks . '/' . $chunkUpload->total_chunks);
        }

        // Validate mime type
        if ($chunkUpload->mime_type) {
            $mimeType = new MimeType($chunkUpload->mime_type);
            if (!$mimeType->isAllowed()) {
                $this->cleanupChunks($chunkUpload);
                throw new \Exception('File type not allowed.');
            }
        }

        // Generate filename and path for final file
        $extension = pathinfo($chunkUpload->original_name, PATHINFO_EXTENSION);
        $filename = Str::uuid() . '.' . $extension;
        $finalPath = 'files/' . date('Y/m/d') . '/' . $filename;
        $fullPath = Storage::path($finalPath);

        // Create directory if it doesn't exist
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Merge chunks into final file
        $finalFile = fopen($fullPath, 'wb');
        
        for ($i = 0; $i < $chunkUpload->total_chunks; $i++) {
            if (!isset($chunkUpload->chunk_paths[$i])) {
                fclose($finalFile);
                throw new \Exception('Missing chunk: ' . $i);
            }

            $chunkContent = file_get_contents($chunkUpload->chunk_paths[$i]);
            fwrite($finalFile, $chunkContent);
        }
        
        fclose($finalFile);

        // Create file entity
        $fileEntity = new FileEntity(
            id: null,
            uuid: null,
            name: $filename,
            originalName: $chunkUpload->original_name,
            size: filesize($fullPath),
            mimeType: $chunkUpload->mime_type,
            extension: $extension,
            path: $fullPath,
            ownerUuid: $ownerUuid,
            uploadDate: new \DateTime(),
        );

        // Save to database
        $this->fileRepository->save($fileEntity);

        // Log activity
        $this->activityLogService->logActivity(
            fileUuid: $fileEntity->uuid,
            action: 'upload',
            ownerUuid: $ownerUuid
        );

        // Cleanup chunks
        $this->cleanupChunks($chunkUpload);

        return $fileEntity;
    }

    /**
     * Cleanup chunk files and database record
     */
    private function cleanupChunks(FileChunk $chunkUpload): void
    {
        // Delete chunk files
        if ($chunkUpload->chunk_paths) {
            foreach ($chunkUpload->chunk_paths as $chunkPath) {
                if (file_exists($chunkPath)) {
                    unlink($chunkPath);
                }
            }
        }

        // Delete chunk directory
        $chunkDir = 'chunks/' . $chunkUpload->upload_id;
        if (Storage::exists($chunkDir)) {
            Storage::deleteDirectory($chunkDir);
        }

        // Delete database record
        $chunkUpload->delete();
    }

    /**
     * Clean up expired chunk uploads (can be called by a scheduled task)
     */
    public function cleanupExpiredUploads(): int
    {
        $expiredUploads = FileChunk::where('expires_at', '<', now())->get();
        
        foreach ($expiredUploads as $upload) {
            $this->cleanupChunks($upload);
        }

        return $expiredUploads->count();
    }
}
