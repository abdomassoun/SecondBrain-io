<?php

namespace App\Application\Users\Handlers;

use App\Application\Users\Commands\ChangeUserPasswordCommand;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class ChangeUserPasswordHandler 
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(ChangeUserPasswordCommand $command)
    {
        $user = $this->userRepository->findById($command->userId);

        if (!$user || !$user->verifyPassword($command->currentPassword)) {
            throw new \Exception('Current password is incorrect');
        }


        $newHash = Hash::make($command->newPassword);

        $user->changePassword($newHash);

        $this->userRepository->update($user);

        return $user;
    }
}

