<?php

namespace App\Application\Users\Handlers;

use App\Application\Users\Commands\ResetPasswordCommand;
use App\Application\Users\Services\OtpService;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Mail;

class ResetPasswordHandler 
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected OtpService $otpService
    ) {}

    public function handle(ResetPasswordCommand $command)
    {
        $user = $this->users->findByEmail($command->email);

        if (!$user) {
            throw new \Exception('User with given email does not exist'); 
        }

        if (!$this->otpService->verifyOtp($command->email, $command->otp)) {
            throw new \Exception('Invalid OTP');
        }

        $user->changePassword(bcrypt($command->newPassword));
        $this->users->update($user);

        $this->otpService->deleteOtp($command->email);

        return true;
    }

}
