<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Users\Controllers\API\V1\UserController;
use App\Presentation\Http\Users\Controllers\API\V1\AuthController;
use App\Presentation\Http\Files\Controllers\API\V1\FileController;

    Route::prefix('auth')->middleware([
        'throttle:auth',
    ])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgetPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::prefix('auth')->middleware([
        'auth:api',
        'throttle:authenticated',
    ])
    ->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::patch('/change-password', [AuthController::class, 'changePassword']);
    });

    Route::prefix('users')->middleware(['auth:api'])->group(function () {
        Route::post('/', [UserController::class, 'create']);
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{uuid}', [UserController::class, 'show']);
        Route::delete('/{uuid}', [UserController::class, 'delete']);
    });

    Route::prefix('files')->middleware(['auth:api'])->group(function () {
        // Simple file upload (with idempotency check)
        Route::post('/upload', [FileController::class, 'upload'])->middleware('idempotent.upload');
        
        // Chunked file upload
        Route::post('/upload-chunk', [FileController::class, 'uploadChunk']);
        Route::post('/complete-upload', [FileController::class, 'completeChunkedUpload'])->middleware('idempotent.upload');
        
        // File management
        Route::get('/', [FileController::class, 'index']);
        Route::get('/my-files', [FileController::class, 'myFiles']);
        Route::get('/{uuid}', [FileController::class, 'show']);
        Route::get('/{uuid}/download', [FileController::class, 'download']);
        Route::delete('/{uuid}', [FileController::class, 'destroy']);
        
        // Activity logs
        Route::get('/{uuid}/activity-logs', [FileController::class, 'activityLogs']);
    });
