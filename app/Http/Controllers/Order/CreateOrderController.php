<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderSideEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Repositories\Asset\AssetRepository;
use App\Services\OrderService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Exception;

class CreateOrderController extends Controller
{
    public function __construct(
        private ResponseService $responseService,
        private OrderService $orderService,
        private AssetRepository $assetRepository,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a limit order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="symbol", type="string", example="BTC"),
     *             @OA\Property(property="side", type="string", example="buy"),
     *             @OA\Property(property="price", type="number", format="float", example=50000),
     *             @OA\Property(property="amount", type="number", format="float", example=0.01)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order created successfully"),
     *     @OA\Response(response=400, description="Insufficient balance or assets")
     * )
     */
    public function __invoke(CreateOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        try {
            $order = $this->orderService->createOrder($user, $data);

            // TODO: Trigger matching logic here once implemented
            // $this->orderService->attemptMatch($order);

            $user->refresh();
            $asset = $order->side === OrderSideEnum::SELL
                ? $this->assetRepository->findByUserAndSymbol($user->id, $order->symbol)
                : null;

            return $this->responseService->success(
                status: 201,
                message: 'Order created successfully',
                data: [
                    'order' => [
                        'id'         => $order->id,
                        'symbol'     => $order->symbol,
                        'side'       => $order->side,
                        'price'      => (float) $order->price,
                        'amount'     => (float) $order->amount,
                        'status'     => $order->status,
                        'created_at' => $order->created_at->toISOString(),
                    ],
                    'wallet' => [
                        'usd_balance' => (float) $user->balance,
                        'assets' => $asset ? [
                            'symbol'        => $asset->symbol,
                            'amount'        => (float) $asset->amount,
                            'locked_amount' => (float) $asset->locked_amount,
                            'total'         => (float) ($asset->amount + $asset->locked_amount),
                        ] : null,
                    ],
                ]
            );
        } catch (Exception $e) {
            return $this->responseService->error(400, $e->getMessage());
        }
    }
}
