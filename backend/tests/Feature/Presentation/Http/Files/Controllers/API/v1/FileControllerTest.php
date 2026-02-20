<?php

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    
    // Create a test user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
    
    // Authenticate the user
    $this->token = auth()->login($this->user);
});

afterEach(function () {
    // Clean up
    File::query()->delete();
    User::query()->delete();
});

test('user can upload a file successfully', function () {
    $file = UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload', [
            'file' => $file,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'file' => [
                    'id',
                    'name',
                    'original_name',
                    'size',
                    'size_formatted',
                    'mime_type',
                    'extension',
                    'owner_uuid',
                    'upload_date',
                ],
            ],
        ]);

    expect($response->json('data.file.original_name'))->toBe('test-document.pdf');
    expect($response->json('data.file.mime_type'))->toBe('application/pdf');
    expect($response->json('data.file.owner_uuid'))->toBe($this->user->uuid);

    // Verify file was saved to database
    $this->assertDatabaseHas('files', [
        'original_name' => 'test-document.pdf',
        'owner_uuid' => $this->user->uuid,
    ]);
});

test('uploading file with same original_name returns existing file (idempotency)', function () {
    // First upload
    $file1 = UploadedFile::fake()->create('my-document.pdf', 1024, 'application/pdf');

    $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload', [
            'file' => $file1,
        ]);

    $response1->assertStatus(201);
    $firstFileId = $response1->json('data.file.id');
    $firstFileUuid = $response1->json('data.file.uuid');

    // Second upload with same original_name but different content/size
    $file2 = UploadedFile::fake()->create('my-document.pdf', 2048, 'application/pdf');

    $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload', [
            'file' => $file2,
        ]);

    // Should return 200 OK (not 201 Created) for idempotency
    $response2->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'File already exists',
        ]);

    // Should return the same file (same ID and UUID)
    expect($response2->json('data.file.id'))->toBe($firstFileId);
    expect($response2->json('data.file.uuid'))->toBe($firstFileUuid);
    expect($response2->json('data.file.original_name'))->toBe('my-document.pdf');

    // Verify only one file exists in database
    $fileCount = File::where('original_name', 'my-document.pdf')
        ->where('owner_uuid', $this->user->uuid)
        ->count();
    expect($fileCount)->toBe(1);
});

test('different users can upload files with same original_name', function () {
    // Create another user
    $user2 = User::factory()->create([
        'email' => 'user2@example.com',
        'password' => bcrypt('password123'),
    ]);
    $token2 = auth()->login($user2);

    // First user uploads a file
    $file1 = UploadedFile::fake()->create('shared-name.pdf', 1024, 'application/pdf');
    $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload', [
            'file' => $file1,
        ]);
    $response1->assertStatus(201);

    // Second user uploads a file with same name (should succeed)
    $file2 = UploadedFile::fake()->create('shared-name.pdf', 1024, 'application/pdf');
    $response2 = $this->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('/api/v1/files/upload', [
            'file' => $file2,
        ]);
    $response2->assertStatus(201); // New file for different user

    // Both files should exist
    expect(File::where('original_name', 'shared-name.pdf')->count())->toBe(2);
});

test('user cannot upload file without authentication', function () {
    $file = UploadedFile::fake()->create('test.pdf', 100);

    $response = $this->postJson('/api/v1/files/upload', [
        'file' => $file,
    ]);

    $response->assertStatus(401);
});

test('user can upload a chunk successfully', function () {
    $uploadId = 'test-upload-' . uniqid();
    $chunkData = base64_encode('This is test chunk data');

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload-chunk', [
            'upload_id' => $uploadId,
            'chunk_index' => 0,
            'total_chunks' => 3,
            'chunk_data' => $chunkData,
            'original_name' => 'large-file.pdf',
            'total_size' => 5000000,
            'mime_type' => 'application/pdf',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'upload_id',
                'uploaded_chunks',
                'total_chunks',
                'is_complete',
            ],
        ]);

    expect($response->json('data.uploaded_chunks'))->toBe(1);
    expect($response->json('data.total_chunks'))->toBe(3);
    expect($response->json('data.is_complete'))->toBeFalse();

    // Verify chunk upload record was created
    $this->assertDatabaseHas('file_chunks', [
        'upload_id' => $uploadId,
        'original_name' => 'large-file.pdf',
        'owner_uuid' => $this->user->uuid,
    ]);
});

test('user can complete chunked upload after all chunks are uploaded', function () {
    $uploadId = 'test-upload-' . uniqid();
    $totalChunks = 3;

    // Upload all chunks
    for ($i = 0; $i < $totalChunks; $i++) {
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/files/upload-chunk', [
                'upload_id' => $uploadId,
                'chunk_index' => $i,
                'total_chunks' => $totalChunks,
                'chunk_data' => base64_encode("Chunk $i data"),
                'original_name' => 'large-file.pdf',
                'total_size' => 5000000,
                'mime_type' => 'application/pdf',
            ]);
    }

    // Complete the upload
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/complete-upload', [
            'upload_id' => $uploadId,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'file' => [
                    'id',
                    'original_name',
                    'size',
                ],
            ],
        ]);

    expect($response->json('data.file.original_name'))->toBe('large-file.pdf');

    // Verify file was created in database
    $this->assertDatabaseHas('files', [
        'original_name' => 'large-file.pdf',
        'owner_uuid' => $this->user->uuid,
    ]);

    // Verify chunk record was deleted after completion
    $this->assertDatabaseMissing('file_chunks', [
        'upload_id' => $uploadId,
    ]);
});

test('completing chunked upload with same original_name returns existing file (idempotency)', function () {
    // First, complete a chunked upload
    $uploadId1 = 'test-upload-1-' . uniqid();
    $totalChunks = 2;

    // Upload all chunks for first upload
    for ($i = 0; $i < $totalChunks; $i++) {
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/files/upload-chunk', [
                'upload_id' => $uploadId1,
                'chunk_index' => $i,
                'total_chunks' => $totalChunks,
                'chunk_data' => base64_encode("First chunk $i data"),
                'original_name' => 'video-file.mp4',
                'total_size' => 5000000,
                'mime_type' => 'video/mp4',
            ]);
    }

    // Complete first upload
    $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/complete-upload', [
            'upload_id' => $uploadId1,
        ]);

    $response1->assertStatus(201);
    $firstFileId = $response1->json('data.file.id');
    $firstFileUuid = $response1->json('data.file.uuid');

    // Now try to upload the same file again with different content
    $uploadId2 = 'test-upload-2-' . uniqid();

    // Upload all chunks for second upload (same original_name)
    for ($i = 0; $i < $totalChunks; $i++) {
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/files/upload-chunk', [
                'upload_id' => $uploadId2,
                'chunk_index' => $i,
                'total_chunks' => $totalChunks,
                'chunk_data' => base64_encode("Second chunk $i data"),
                'original_name' => 'video-file.mp4', // Same name
                'total_size' => 6000000,
                'mime_type' => 'video/mp4',
            ]);
    }

    // Complete second upload - should return existing file
    $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/complete-upload', [
            'upload_id' => $uploadId2,
        ]);

    // Should return 200 OK (not 201 Created) for idempotency
    $response2->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'File already exists',
        ]);

    // Should return the same file
    expect($response2->json('data.file.id'))->toBe($firstFileId);
    expect($response2->json('data.file.uuid'))->toBe($firstFileUuid);

    // Verify only one file exists in database
    $fileCount = File::where('original_name', 'video-file.mp4')
        ->where('owner_uuid', $this->user->uuid)
        ->count();
    expect($fileCount)->toBe(1);

    // Verify second chunk upload was cleaned up
    $this->assertDatabaseMissing('file_chunks', [
        'upload_id' => $uploadId2,
    ]);
});

test('user can retrieve their files', function () {
    // Create some test files
    File::create([
        'name' => 'file1.pdf',
        'original_name' => 'Document 1.pdf',
        'size' => 1024,
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'path' => '/storage/files/file1.pdf',
        'owner_uuid' => $this->user->uuid,
        'upload_date' => now(),
    ]);

    File::create([
        'name' => 'file2.jpg',
        'original_name' => 'Image 1.jpg',
        'size' => 2048,
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'path' => '/storage/files/file2.jpg',
        'owner_uuid' => $this->user->uuid,
        'upload_date' => now(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/v1/files/my-files');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

    expect(count($response->json('data')))->toBe(2);
});

test('user can delete their own file', function () {
    $file = File::create([
        'name' => 'file1.pdf',
        'original_name' => 'Document 1.pdf',
        'size' => 1024,
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'path' => '/tmp/test-file.pdf',
        'owner_uuid' => $this->user->uuid,
        'upload_date' => now(),
    ]);

    // Create a dummy file
    file_put_contents('/tmp/test-file.pdf', 'test content');

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->deleteJson("/api/v1/files/{$file->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'File deleted successfully',
        ]);

    // Verify file was soft deleted
    $this->assertSoftDeleted('files', [
        'uuid' => $file->uuid,
    ]);

    // Verify activity log was created
    $this->assertDatabaseHas('file_activity_logs', [
        'file_uuid' => $file->uuid,
        'user_id' => $this->user->id,
        'action' => 'delete',
    ]);
});

test('user cannot delete another users file', function () {
    // Create another user
    $otherUser = User::factory()->create([
        'email' => 'other@example.com',
    ]);

    $file = File::create([
        'name' => 'file1.pdf',
        'original_name' => 'Document 1.pdf',
        'size' => 1024,
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'path' => '/storage/files/file1.pdf',
        'owner_uuid' => $otherUser->uuid,
        'upload_date' => now(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->deleteJson("/api/v1/files/{$file->id}");

    $response->assertStatus(400);

    // Verify file was NOT deleted
    $this->assertDatabaseHas('files', [
        'uuid' => $file->uuid,
        'deleted_at' => null,
    ]);
});

test('user can view file details', function () {
    $file = File::create([
        'name' => 'file1.pdf',
        'original_name' => 'Document 1.pdf',
        'size' => 1024,
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'path' => '/storage/files/file1.pdf',
        'owner_uuid' => $this->user->uuid,
        'upload_date' => now(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson("/api/v1/files/{$file->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'file' => [
                    'id',
                    'name',
                    'original_name',
                    'size',
                    'mime_type',
                ],
            ],
        ]);

    expect($response->json('data.file.id'))->toBe($file->id);
    expect($response->json('data.file.original_name'))->toBe('Document 1.pdf');
});

test('upload validates file is required', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

test('chunk upload validates required fields', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload-chunk', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'upload_id',
            'chunk_index',
            'total_chunks',
            'chunk_data',
            'original_name',
            'total_size',
        ]);
});

test('activity logs are created for file operations', function () {
    $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/v1/files/upload', [
            'file' => $file,
        ]);

    $fileId = $response->json('data.file.id');

    // Verify upload activity log
    $this->assertDatabaseHas('file_activity_logs', [
        'file_uuid' => $fileId,
        'user_id' => $this->user->id,
        'action' => 'upload',
    ]);
});
