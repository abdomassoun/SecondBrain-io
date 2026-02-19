<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
