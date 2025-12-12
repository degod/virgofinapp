<?php

namespace App\Repositories\Order;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(private Order $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?Order
    {
        return $this->model->find($id);
    }

    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function getUserOrders(int $userId, ?string $symbol = null): Collection
    {
        $query = $this->model->where('user_id', $userId);
        if ($symbol) {
            $query->where('symbol', $symbol);
        }
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getOpenOrdersBySymbol(string $symbol): Collection
    {
        return $this->model->where('symbol', $symbol)
            ->where('status', 1) // open
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
