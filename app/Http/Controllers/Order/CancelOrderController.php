<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\OrderService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelOrderController extends Controller
{
    public function __construct(
        private ResponseService $responseService,
        private OrderService $orderService,
        private OrderRepositoryInterface $orderRepository,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/cancel",
     *     summary="Cancel an open order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order cancelled successfully"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=400, description="Order cannot be cancelled")
     * )
     */
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = $this->orderRepository->findById($id);

        if (! $order || $order->user_id !== $user->id) {
            return $this->responseService->error(404, 'Order not found');
        }

        try {
            $this->orderService->cancelOrder($order);

            return $this->responseService->success(
                status: 200,
                message: 'Order cancelled successfully',
                data: [
                    'order' => [
                        'id'     => $order->id,
                        'symbol' => $order->symbol,
                        'side'   => $order->side,
                        'status' => $order->status,
                    ],
                    'wallet' => $order->side === 'buy'
                        ? ['usd_balance' => (float) $user->refresh()->balance]
                        : ['assets' => $this->orderService->getUserAssetState($user, $order->symbol)],
                ]
            );
        } catch (Exception $e) {
            return $this->responseService->error(400, $e->getMessage());
        }
    }
}
