<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListOrdersController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ResponseService $responseService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="List authenticated user's orders with optional filters",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Pagination page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="orderbook",
     *         in="query",
     *         required=false,
     *         description="Fetch orders for order book (true/false)",
     *         @OA\Schema(type="string", example="true")
     *     ),
     *     @OA\Parameter(
     *         name="symbol",
     *         in="query",
     *         required=false,
     *         description="Filter orders by asset symbol",
     *         @OA\Schema(type="string", example="BTC")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter orders by status (open, filled, cancelled)",
     *         @OA\Schema(type="string", example="open")
     *     ),
     *     @OA\Parameter(
     *         name="side",
     *         in="query",
     *         required=false,
     *         description="Filter orders by side (buy/sell)",
     *         @OA\Schema(type="string", example="buy")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully"
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['symbol', 'status', 'side', 'orderbook']);

        $orders = $this->orderRepository->paginateUserOrders(
            userId: $user->id,
            filters: $filters,
            perPage: $request->integer('per_page', 10)
        );

        return $this->responseService->successPaginated(
            code: 200,
            message: 'Orders retrieved successfully',
            paginator: $orders
        );
    }
}
