<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $users,
        private ResponseService $response
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = $this->users->findByEmail($request->email);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->response->error(
                status: 401,
                message: 'Invalid login credentials.'
            );
        }

        $token = $user->createToken('api')->plainTextToken;

        return $this->response->success(
            status: 200,
            message: 'Login successful.',
            data: [
                'access_token' => $token,
                'token_type'   => 'Bearer',
            ]
        );
    }
}
