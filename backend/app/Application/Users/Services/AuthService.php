<?php

namespace App\Application\Users\Services;

use App\Domain\Users\Entities\User;
use App\Infrastructure\Persistence\Eloquent\Models\User as EloquentUser;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function createToken(User $user, string $deviceName = 'api'): string
    {
        $eloquentUser = EloquentUser::find($user->id);
        return JWTAuth::fromUser($eloquentUser);
    }

    public function revokeToken(EloquentUser $user): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refreshToken(EloquentUser $user, string $deviceName = 'api'): string
    {
        return JWTAuth::refresh(JWTAuth::getToken());
    }
}

