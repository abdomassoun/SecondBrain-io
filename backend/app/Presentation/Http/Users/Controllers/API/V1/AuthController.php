<?php

namespace App\Presentation\Http\Users\Controllers\API\V1;

use App\Application\Users\Commands\ChangeUserPasswordCommand;
use App\Application\Users\Commands\ForgotPasswordCommand;
use App\Application\Users\Commands\LoginUserCommand;
use App\Application\Users\Commands\RegisterUserCommand;
use App\Application\Users\Commands\ResetPasswordCommand;
use App\Application\Users\DTOs\AuthUserDTO;
use App\Application\Users\Handlers\ChangeUserPasswordHandler;
use App\Application\Users\Handlers\ForgotPasswordHandler;
use App\Application\Users\Handlers\LoginUserHandler;
use App\Application\Users\Handlers\RegisterUserHandler;
use App\Application\Users\Handlers\ResetPasswordHandler;
use App\Application\Users\Services\AuthService;
use App\Presentation\Http\Controller;
use App\Presentation\Http\Users\Requests\API\V1\ChangeUserPasswordRequest;
use App\Presentation\Http\Users\Requests\API\V1\ForgotUserPasswordRequest;
use App\Presentation\Http\Users\Requests\API\V1\LoginUserRequest;
use App\Presentation\Http\Users\Requests\API\V1\RegisterUserRequest;
use App\Presentation\Http\Users\Requests\API\V1\ResetPasswordRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request, RegisterUserHandler $handler, AuthService $authService)
    {
        $data = $request->validated();

        $command = new RegisterUserCommand($data['email'], $data['password']);

        try {
            $user  = $handler->handle($command);
            $token = $authService->createToken($user);
            $authUserDTO = AuthUserDTO::fromEntity($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success([
            'user' => $authUserDTO,
            'token' => $token,
        ], 'Registration successful');
    }

    public function login(LoginUserRequest $request, LoginUserHandler $handler, AuthService $authService)
    {
        $data = $request->validated();

        $command = new LoginUserCommand($data['email'], $data['password']);

        try {
            $user  = $handler->handle($command);
            $token = $authService->createToken($user);
            $authUserDTO = AuthUserDTO::fromEntity($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success([
            'user' => $authUserDTO,
            'token' => $token,
        ], 'Login successful');
    }

    public function refreshToken(Request $request, AuthService $authService)
    {
        $newToken = $authService->refreshToken($request->user());
        return $this->success(['token' => $newToken], 'Token refreshed successfully');
    }

    public function logout(Request $request, AuthService $authService)
    {
        $authService->revokeToken($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        $authUserDTO = AuthUserDTO::fromModel($request->user());
        return $this->success($authUserDTO, 'User retrieved successfully');
    }


    public function changePassword(ChangeUserPasswordRequest $request, ChangeUserPasswordHandler $handler , AuthService $authService)
    {

        $data = $request->validated();

        $command = new ChangeUserPasswordCommand(
            $request->user()->id,
            $data['current_password'],
            $data['new_password']
        );
        try {
            $user = $handler->handle($command);
            $authService->revokeToken($request->user());
            $newToken = $authService->createToken($user);
            $authUserDTO = AuthUserDTO::fromEntity($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success([
            'user' => $authUserDTO,
            'token' => $newToken,
        ], 'Password changed successfully');
    }
    
    public function forgetPassword(ForgotUserPasswordRequest $request, ForgotPasswordHandler $handler)
    {
        
        $data = $request->validated();

        try{
            $handler->handle(new ForgotPasswordCommand($data['email']));
        }catch(\Exception $e){
            return $this->error($e->getMessage(), 400);
        }

        return $this->success([], 'OTP has been sent to your email');
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordHandler $handler)
    {
        try{

            $handler->handle(
                new ResetPasswordCommand(
                    $request->email,
                    $request->otp,
                    $request->new_password
                    )
                );
        }catch(\Exception $e){
            return $this->error($e->getMessage(), 400);
        }

        return $this->success([], 'Password reset successfully');
    }

}

