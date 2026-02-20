<?php

namespace App\Console\Commands;

use App\Application\Files\Services\FileUploadService;
use Illuminate\Console\Command;

class CleanupExpiredFileChunks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:cleanup-chunks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired file chunk uploads';

    /**
     * Execute the console command.
     */
    public function handle(FileUploadService $uploadService): int
    {
        $this->info('Cleaning up expired file chunks...');

        $count = $uploadService->cleanupExpiredUploads();

        $this->info("Cleaned up {$count} expired chunk upload(s).");

        return Command::SUCCESS;
    }
}
