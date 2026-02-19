<?php

namespace App\Application\Users\Services;

use App\Application\Users\DTOs\UserDTO;
use App\Application\Users\Queries\GetUsersQuery;
use App\Infrastructure\Persistence\Eloquent\Models\User;

class UserQueryService
{
    protected $authUser;

    public function __construct($authUser)
    {
        $this->authUser = $authUser;
    }

    public function search(GetUsersQuery $query)
    {
        $usersQuery = User::query();

        if ($query->email) {
            $usersQuery->where('email', 'LIKE', "%{$query->email}%");
        }

        return $usersQuery->paginate($query->limit)
            ->through(fn ($model) => UserDTO::fromModel($model));
    }

    public function getUserByUuid(string $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        return UserDTO::fromModel($user);
    }
}
