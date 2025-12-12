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
     *     summary="List authenticated user's orders",
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
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully"
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = $this->orderRepository->paginateUserOrders(
            userId: $user->id,
            perPage: $request->integer('per_page', 10)
        );

        return $this->responseService->successPaginated(
            code: 200,
            message: 'Orders retrieved successfully',
            paginator: $orders
        );
    }
}
