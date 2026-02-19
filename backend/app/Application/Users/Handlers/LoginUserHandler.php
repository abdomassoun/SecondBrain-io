<?php

namespace App\Application\Users\Handlers;

use App\Application\Users\Commands\LoginUserCommand;
use App\Domain\Users\Repositories\UserRepositoryInterface;

class LoginUserHandler
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(LoginUserCommand $command)
    {
        $user = $this->userRepository->findByEmail($command->email);

        if (!$user){
            throw new \Exception('No account is associated with this email. Please check the address and try again.');
        }

        if(!$user->verifyPassword($command->password)) {
            throw new \Exception('Invalid password, please try again');
        }

        return $user;
    }
}

