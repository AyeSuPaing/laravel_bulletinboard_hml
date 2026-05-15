<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginApiRequest;
use App\Http\Requests\Api\PasswordChangeRequest;
use App\Http\Requests\Api\PasswordResetConfirmRequest;
use App\Http\Requests\Api\PasswordResetRequest;
use App\Http\Resources\Api\TableColumnResource;
use App\Services\Api\AuthService;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * User service instance
     *
     * @var \App\Services\AuthService
     */
    protected $auth_service;
    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->auth_service = new AuthService();
    }

    /**
     * login
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function login(LoginApiRequest $request): JsonResponse
    {
        $success_data = $this->auth_service->login($request);
        return $this->success($success_data);
    }

    /**
     * logout
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $success_msg = $this->auth_service->logout($request);
        return $this->success($success_msg);
    }

    /**
     * resetPasswordMail
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function resetPasswordMail(PasswordResetRequest $request): JsonResponse
    {
        $success_msg = $this->auth_service->resetPasswordMail($request);
        return $this->success($success_msg);
    }

    /**
     * resetPassword
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function resetPassword(PasswordResetConfirmRequest $request): JsonResponse
    {
        $this->auth_service->updatePassword($request);
        return $this->success(["message" => "Password reset is successfully."]);
    }

    /**
     * changePassword
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function changePassword(PasswordChangeRequest $request): JsonResponse
    {
        $this->auth_service->updatePassword($request);
        return $this->success(["message" => "Password change is successfully."]);
    }

    /**
     * getAuthSession
     *
     * @return void
     */
    public function getAuthSession(Request $request): JsonResponse
    {
        $success_data = $this->auth_service->getAuthSession($request);
        return $this->success($success_data);
    }
    
    /**
     * getTableConfigs
     *
     * @return JsonResponse
     */
    public function getTableConfigs(): JsonResponse
    {
        $columns = $this->auth_service->getTableConfigs();
        $response = $columns->map(function ($tableColumns) {
            return TableColumnResource::collection($tableColumns);
        });
        return $this->success($response->toArray());
    }
}
