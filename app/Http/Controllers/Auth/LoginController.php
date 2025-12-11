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

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Authenticate user and return access token",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login credentials",
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="test@mail.com"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="1|abcdef12345"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid login credentials.")
     *         )
     *     )
     * )
     */
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
