<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderSideEnum;
use App\Http\Controllers\Controller;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;

class OrderSideOptionController extends Controller
{
    public function __construct(
        private ResponseService $responseService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/orders/sides",
     *     summary="Get order side options",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Order side options retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="Buy"),
     *                     @OA\Property(property="value", type="string", example="buy"),
     *                     @OA\Property(property="selected", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function __invoke(): JsonResponse
    {
        $sides = OrderSideEnum::getSides();

        $options = array_merge(
            [
                [
                    'name'     => "Select an option",
                    'value'    => "",
                ],
            ],
            array_map(
                fn($side) => [
                    'name'     => ucfirst($side),
                    'value'    => $side,
                ],
                $sides,
            )
        );

        return $this->responseService->success(
            message: 'Order side options retrieved successfully',
            data: $options
        );
    }
}
