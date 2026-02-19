<?php

namespace App\Presentation\Http\Users\Controllers\API\V1;

use App\Application\Users\Commands\CreateUserCommand;
use App\Application\Users\DTOs\UserDTO;
use App\Application\Users\Handlers\CreateUserHandler;
use App\Application\Users\Queries\GetUsersQuery;
use App\Application\Users\Services\UserQueryService;
use App\Presentation\Http\Controller;
use App\Presentation\Http\Users\Requests\API\V1\CreateUserRequest;
use App\Presentation\Http\Users\Resources\API\V1\UserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = new GetUsersQuery(
                email: $request->query->get('email'),
                limit: (int) $request->query->get('limit', 15),
                offset: (int) $request->query->get('offset', 0)
            );
            $users = (new UserQueryService(auth()->user()))->search($query);
            return $this->paginatedSuccess($users,'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(string $uuid)
    {
        try {
            $user = (new UserQueryService(auth()->user()))->getUserByUuid($uuid);
            return $this->success(['user' => $user], 'User retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error('User not found', 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function create(CreateUserRequest $request, CreateUserHandler $handler)
    {
        $command = new CreateUserCommand(
            email: $request->email,
            password: $request->password,
        );
        
        try {
            $user = $handler->handle($command);
            $userDTO = UserDTO::fromEntity($user); 
            
            return $this->success(['user' => new UserResource($userDTO)],'User created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
