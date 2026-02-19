<?php

namespace App\Application\Users\Handlers;

use App\Application\Users\Commands\ForgotPasswordCommand;
use App\Application\Users\Services\OtpService;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordHandler
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected OtpService $otpService
    ) {}

    public function handle(ForgotPasswordCommand $command)
    {
        $user = $this->users->findByEmail($command->email);

        if (!$user) {
            throw new \Exception('User with given email does not exist'); 
        }

        $otp = $this->otpService->generateOtp();
        $this->otpService->storeOtp($user->email, $otp);

        // Mail::raw("Your password reset code is: {$otp}", function ($message) use ($user) {
        //     $message->to($user->email)->subject('Password Reset Code');
        // });

        return true;
    }
}
