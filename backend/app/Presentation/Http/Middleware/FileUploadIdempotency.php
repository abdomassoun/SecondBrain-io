<?php

namespace App\Presentation\Http\Middleware;

use App\Application\Files\DTOs\FileDTO;
use App\Domain\Files\Repositories\FileRepositoryInterface;
use App\Presentation\Http\Files\Resources\API\V1\FileResource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FileUploadIdempotency
{
    public function __construct(
        private FileRepositoryInterface $fileRepository
    ) {}

    /**
     * Handle an incoming request.
     * Check if a file with the same original_name already exists for the user.
     * If it does, return the existing file info with 200 OK status.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('[Idempotency] Middleware invoked', ['path' => $request->path()]);
        
        // Only apply to authenticated users
        if (!$request->user()) {
            Log::info('[Idempotency] No authenticated user, skipping');
            return $next($request);
        }

        $ownerUuid = $request->user()->uuid;
        $originalName = null;

        // Determine the original filename based on the request type
        if ($request->hasFile('file')) {
            // Simple file upload
            $originalName = $request->file('file')->getClientOriginalName();
            Log::info('[Idempotency] Simple upload detected', ['original_name' => $originalName]);
        } elseif ($request->has('original_name')) {
            // Chunked upload (upload-chunk endpoint)
            $originalName = $request->input('original_name');
            Log::info('[Idempotency] Chunk upload detected', ['original_name' => $originalName]);
        } elseif ($request->has('upload_id')) {
            // Complete chunked upload - need to fetch original_name from FileChunk
            $uploadId = $request->input('upload_id');
            Log::info('[Idempotency] Complete upload detected', ['upload_id' => $uploadId]);
            $chunkUpload = \App\Infrastructure\Persistence\Eloquent\Models\FileChunk::where('upload_id', $uploadId)
                ->where('owner_uuid', $ownerUuid)
                ->first();
            
            if ($chunkUpload) {
                $originalName = $chunkUpload->original_name;
                Log::info('[Idempotency] Found chunk upload', ['original_name' => $originalName]);
            } else {
                Log::warning('[Idempotency] Chunk upload not found', ['upload_id' => $uploadId, 'owner_uuid' => $ownerUuid]);
            }
        }

        // If we couldn't determine the original name, proceed normally
        if (!$originalName) {
            Log::info('[Idempotency] No original name found, proceeding with upload');
            return $next($request);
        }

        // Check if file with same original_name already exists for this user
        $existingFile = $this->findFileByOriginalName($originalName, $ownerUuid);

        if ($existingFile) {
            Log::info('[Idempotency] File already exists, returning existing file', [
                'file_id' => $existingFile->id,
                'original_name' => $originalName
            ]);
            // File already exists - return it with 200 OK (idempotent response)
            $fileDTO = FileDTO::fromEntity($existingFile);
            
            // If this is a complete-upload request with chunks, clean them up
            if ($request->has('upload_id')) {
                $uploadId = $request->input('upload_id');
                $chunkUpload = \App\Infrastructure\Persistence\Eloquent\Models\FileChunk::where('upload_id', $uploadId)
                    ->where('owner_uuid', $ownerUuid)
                    ->first();
                
                if ($chunkUpload) {
                    // Cleanup chunks since file already exists
                    $this->cleanupChunks($chunkUpload);
                }
            }
            
            return response()->json([
                'status' => true,
                'message' => 'File already exists',
                'data' => [
                    'file' => new FileResource($fileDTO)
                ]
            ], 200);
        }

        Log::info('[Idempotency] File does not exist, proceeding with upload', ['original_name' => $originalName]);
        // File doesn't exist - proceed with the upload
        return $next($request);
    }

    /**
     * Find a file by original name and owner UUID
     */
    private function findFileByOriginalName(string $originalName, string $ownerUuid)
    {
        return $this->fileRepository->findByOriginalNameAndOwner($originalName, $ownerUuid);
    }

    /**
     * Cleanup chunk files and database record
     */
    private function cleanupChunks($chunkUpload): void
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
        if (\Illuminate\Support\Facades\Storage::exists($chunkDir)) {
            \Illuminate\Support\Facades\Storage::deleteDirectory($chunkDir);
        }

        // Delete database record
        $chunkUpload->delete();
    }
}
