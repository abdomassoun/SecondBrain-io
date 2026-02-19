<?php

namespace App\Application\Users\DTOs;

use App\Domain\Users\Entities\User as UserEntity;
use App\Infrastructure\Persistence\Eloquent\Models\User;

final class AuthUserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly string $email,
        public readonly \DateTime $createdAt,
        public readonly \DateTime $updatedAt,
    ) {}

    public static function fromModel(User $model): self
    {
        return new self(
            id: $model->id,
            uuid: $model->uuid,
            email: $model->email,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public static function fromEntity(UserEntity $entity): self
    {
        $model = User::where('uuid', $entity->uuid)->firstOrFail();
        return self::fromModel($model);
    }
}
