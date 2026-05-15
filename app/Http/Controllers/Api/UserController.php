<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileSaveRequest;
use App\Http\Requests\Api\UserDeleteRequest;
use App\Http\Requests\Api\UserSaveRequest;
use App\Http\Requests\Api\UserSearchRequest;
use App\Http\Requests\Api\UserUnlockRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\Api\UserService;
use App\Traits\ApiResponseTrait;

class UserController extends Controller
{
    use ApiResponseTrait;

    /**
     * User service instance
     *
     * @var \App\Services\UserService
     */
    protected $user_service;

    /**
     * auth_user
     *
     * @var mixed
     */
    protected $auth_user;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->user_service = new UserService();
        $this->auth_user = auth('sanctum')->user();
        if (!$this->auth_user) {
            return $this->error(["Unauthorized."], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * getUserList
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function getUserList(UserSearchRequest $request): JsonResponse
    {
        // Fetch user list using the user service
        $users = $this->user_service->list($request);
        $response = UserResource::collection($users);
        return $this->paginate($response);
    }

    /**
     * getUser
     *
     * @param  mixed $id
     * @return JsonResponse
     */
    public function getUser($id): JsonResponse
    {
        $user = $this->user_service->getUserById($id);
        $response = new UserResource($user);
        return $this->success($response->resolve());
    }

    /**
     * createUser
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function createUser(UserSaveRequest $request): JsonResponse
    {
        $user = $this->user_service->save($request);
        $response = new UserResource($user);
        return $this->success($response->resolve());
    }

    /**
     * updateUser
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return JsonResponse
     */
    public function updateUser(UserSaveRequest $request, $id = null): JsonResponse
    {
        $user = $this->user_service->save($request, $id);
        $response = new UserResource($user);
        return $this->success($response->resolve());
    }

    /**
     * profile
     *
     * @return JsonResponse
     */
    public function profile(): JsonResponse
    {
        $user_id = $this->auth_user->id;
        $user = $this->user_service->getUserById($user_id);
        $response = new UserResource($user);
        return $this->success($response->resolve());
    }

    /**
     * updateProfile
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function updateProfile(ProfileSaveRequest $request): JsonResponse
    {
        $user_id = $this->auth_user->id;
        $user = $this->user_service->save($request, $user_id);
        $response = new UserResource($user);
        return $this->success($response->resolve());
    }

    /**
     * unlockUsers
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function unlockUsers(UserUnlockRequest $request): JsonResponse
    {
        $success_msg = $this->user_service->unlock($request);
        return $this->success($success_msg);
    }

    /**
     * deleteUsers
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function deleteUsers(UserDeleteRequest $request): JsonResponse
    {
        $success_msg = $this->user_service->delete($request);
        return $this->success($success_msg);
    }
}
