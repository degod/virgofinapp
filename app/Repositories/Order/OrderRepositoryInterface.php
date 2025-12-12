<?php

namespace App\Repositories\Order;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    public function findById(int $id): ?Order;
    public function update(Order $order, array $data): bool;
    public function getUserOrders(int $userId, ?string $symbol = null): Collection;
    public function getOpenOrdersBySymbol(string $symbol): Collection;
}
