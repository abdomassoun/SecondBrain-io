<?php

namespace App\Application\Users\Handlers;

use App\Application\Users\Commands\RegisterUserCommand;
use App\Domain\Users\Entities\User as UserEntity;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function handle(RegisterUserCommand $command): UserEntity
    {
        $user = new UserEntity(
            id: null,
            uuid: null,
            email: $command->email,
            password: $command->password,
        );
        
        $this->userRepository->save($user);

        return $user;
    }
}
