<?php

namespace App\Presentation\Http\Files\Controllers\API\V1;

use App\Application\Files\Commands\CompleteChunkedUploadCommand;
use App\Application\Files\Commands\DeleteFileCommand;
use App\Application\Files\Commands\UploadChunkCommand;
use App\Application\Files\Commands\UploadFileCommand;
use App\Application\Files\DTOs\FileDTO;
use App\Application\Files\Handlers\CompleteChunkedUploadHandler;
use App\Application\Files\Handlers\DeleteFileHandler;
use App\Application\Files\Handlers\UploadChunkHandler;
use App\Application\Files\Handlers\UploadFileHandler;
use App\Application\Files\Queries\GetFileByIdQuery;
use App\Application\Files\Queries\GetFilesQuery;
use App\Application\Files\Services\FileActivityLogService;
use App\Application\Files\Services\FileQueryService;
use App\Presentation\Http\Controller;
use App\Presentation\Http\Files\Requests\API\V1\CompleteChunkedUploadRequest;
use App\Presentation\Http\Files\Requests\API\V1\UploadChunkRequest;
use App\Presentation\Http\Files\Requests\API\V1\UploadFileRequest;
use App\Presentation\Http\Files\Resources\API\V1\FileResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        private FileActivityLogService $activityLogService
    ) {
        parent::__construct();
    }

    /**
     * Get all files (with optional filtering)
     */
    public function index(Request $request)
    {
        try {
            $query = new GetFilesQuery(
                ownerUuid: $request->query('owner_uuid'),
                mimeType: $request->query('mime_type'),
                limit: (int) $request->query('limit', 15),
                offset: (int) $request->query('offset', 0)
            );

            $files = (new FileQueryService())->search($query);

            return $this->paginatedSuccess($files, 'Files retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get files for the authenticated user
     */
    public function myFiles(Request $request)
    {
        try {
            $query = new GetFilesQuery(
                ownerUuid: $request->user()->uuid,
                mimeType: $request->query('mime_type'),
                limit: (int) $request->query('limit', 15),
                offset: (int) $request->query('offset', 0)
            );

            $files = (new FileQueryService())->search($query);

            return $this->paginatedSuccess($files, 'Your files retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Upload a single file (non-chunked)
     */
    public function upload(UploadFileRequest $request, UploadFileHandler $handler)
    {
        try {
            $command = new UploadFileCommand(
                file: $request->file('file'),
                ownerUuid: $request->user()->uuid
            );

            $file = $handler->handle($command);
            $fileDTO = FileDTO::fromEntity($file);

            return $this->created(
                ['file' => new FileResource($fileDTO)],
                'File uploaded successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Upload a chunk of a file
     */
    public function uploadChunk(UploadChunkRequest $request, UploadChunkHandler $handler)
    {
        try {
            $command = new UploadChunkCommand(
                uploadId: $request->input('upload_id'),
                chunkIndex: $request->input('chunk_index'),
                totalChunks: $request->input('total_chunks'),
                originalName: $request->input('original_name'),
                totalSize: $request->input('total_size'),
                chunkData: $request->input('chunk_data'),
                mimeType: $request->input('mime_type'),
                ownerUuid: $request->user()->uuid
            );

            $result = $handler->handle($command);

            return $this->success($result, 'Chunk uploaded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Complete chunked upload
     */
    public function completeChunkedUpload(CompleteChunkedUploadRequest $request, CompleteChunkedUploadHandler $handler)
    {
        try {
            $command = new CompleteChunkedUploadCommand(
                uploadId: $request->input('upload_id'),
                ownerUuid: $request->user()->uuid
            );

            $file = $handler->handle($command);
            $fileDTO = FileDTO::fromEntity($file);

            return $this->created(
                ['file' => new FileResource($fileDTO)],
                'File upload completed successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Get file details
     */
    public function show(string $uuid, Request $request)
    {
        try {
            $query = new GetFileByIdQuery(
                fileUuid: $uuid,
                ownerUuid: null // Allow viewing any file metadata (download will check ownership)
            );

            $file = (new FileQueryService())->getFileById($query);

            return $this->success(
                ['file' => new FileResource($file)],
                'File retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFound('File not found');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Download a file
     */
    public function download(string $uuid, Request $request)
    {
        try {
            $query = new GetFileByIdQuery(
                fileUuid: $uuid,
                ownerUuid: $request->user()->uuid // Only allow downloading own files
            );

            $file = (new FileQueryService())->getFileById($query);

            // Log download activity
            $this->activityLogService->logActivity(
                fileUuid: $uuid,
                action: 'download',
                userId: $request->user()->id
            );

            // Get file path
            $filePath = \App\Infrastructure\Persistence\Eloquent\Models\File::where('uuid', $uuid)->first()->path;

            if (!file_exists($filePath)) {
                return $this->error('File not found on disk', 404);
            }

            return response()->download($filePath, $file->originalName);
        } catch (ModelNotFoundException $e) {
            return $this->notFound('File not found or you do not have permission to download it');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Delete a file
     */
    public function destroy(string $uuid, Request $request, DeleteFileHandler $handler)
    {
        try {
            $command = new DeleteFileCommand(
                fileUuid: $uuid,
                ownerUuid: $request->user()->uuid
            );

            $handler->handle($command);

            return $this->deleted('File deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Get activity logs for a file
     */
    public function activityLogs(string $uuid, Request $request)
    {
        try {
            // Verify ownership
            $query = new GetFileByIdQuery(
                fileUuid: $uuid,
                ownerUuid: $request->user()->uuid
            );

            (new FileQueryService())->getFileById($query);

            $logs = $this->activityLogService->getFileActivityLogs($uuid);

            return $this->success(['logs' => $logs], 'Activity logs retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->notFound('File not found or you do not have permission to view it');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
