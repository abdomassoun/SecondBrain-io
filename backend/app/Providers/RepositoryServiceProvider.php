<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\UserRepository;
use App\Domain\Files\Repositories\FileRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\FileRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
