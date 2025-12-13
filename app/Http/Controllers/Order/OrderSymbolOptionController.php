<?php

namespace App\Http\Controllers\Order;

use App\Enums\AssetEnum;
use App\Http\Controllers\Controller;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;

class OrderSymbolOptionController extends Controller
{
    public function __construct(
        private ResponseService $responseService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/orders/symbols",
     *     summary="Get tradable symbol options",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Symbol options retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="BTC"),
     *                     @OA\Property(property="value", type="string", example="BTC"),
     *                     @OA\Property(property="selected", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function __invoke(): JsonResponse
    {
        $symbols = AssetEnum::symbols();

        $options = array_merge(
            [
                [
                    'name'     => "Select an Asset",
                    'value'    => "",
                ],
            ],
            array_map(
                fn($symbol) => [
                    'name'     => $symbol,
                    'value'    => $symbol,
                ],
                $symbols,
            )
        );

        return $this->responseService->success(
            message: 'Symbol options retrieved successfully',
            data: $options
        );
    }
}
