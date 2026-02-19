<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Users\Entities\User as UserEntity;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?UserEntity
    {
        $user = User::where('email', $email)->first();

        if (!$user) return null;
        return $user->toDomainEntity();
    }

    public function findById(string $id): ?UserEntity
    {
        $user = User::find($id);
        if (!$user) return null;

        return $user->toDomainEntity();
    }

    public function findByUuid(string $uuid): ?UserEntity
    {
        $user = User::where('uuid', $uuid)->first();
        if (!$user) return null;

        return $user->toDomainEntity();
    }

    public function findAll(): array
    {
        return User::all()
            ->map(fn($model) => $model->toDomainEntity())
            ->toArray();
    }

    public function save(UserEntity $user): void
    {
        DB::transaction(function () use ($user) {
            $model = new User();
            $model->email = $user->email;
            $model->password = $user->password;
            $model->save();
            
            $user->id   = $model->id;
            $user->uuid = $model->uuid;
        });
    }

    public function update(UserEntity $user): void
    {
        DB::transaction(function () use ($user) {
            $model = User::find($user->id);

            if (! $model) {
                throw new \RuntimeException('User not found for update.');
            }

            $model->email = $user->email;
            $model->password = $user->password;
            $model->save();
        });
    }

    public function delete(UserEntity $user): void
    {
        User::where('id', $user->id)->delete();
    }

    public function changePassword(string $userId, string $hashedPassword): void
    {
        User::where('id', $userId)->update([
            'password' => $hashedPassword,
        ]);
    }
}
