<?php

namespace App\Http\Controllers\User;

use App\Enums\AssetEnum;
use App\Http\Controllers\Controller;
use App\Repositories\Asset\AssetRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ResponseService $responseService,
        private AssetRepositoryInterface $assetRepository
    ) {}

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get authenticated user's balance and assets",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile with USD balance and assets",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="balance", type="number", format="float", example=1000.50),
     *                 @OA\Property(
     *                     property="assets",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="symbol", type="string", example="BTC"),
     *                         @OA\Property(property="amount", type="number", format="float", example=0.5),
     *                         @OA\Property(property="locked_amount", type="number", format="float", example=0.1)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $supportedSymbols = AssetEnum::symbols();
        $userAssets = $this->assetRepository->getUserAssets($user->id)->keyBy('symbol');

        $assets = collect($supportedSymbols)->map(function (string $symbol) use ($userAssets) {
            $asset = $userAssets->get($symbol);

            return [
                'symbol'        => $symbol,
                'amount'        => $asset ? (float) $asset->amount : 0.0,
                'locked_amount' => $asset ? (float) $asset->locked_amount : 0.0,
                'total'         => $asset ? (float) ($asset->amount + $asset->locked_amount) : 0.0,
            ];
        })->values()->toArray();

        return $this->responseService->success(
            status: 200,
            message: 'Profile retrieved successfully',
            data: [
                'balance' => (float) $user->balance,
                'assets'  => $assets,
            ]
        );
    }
}
