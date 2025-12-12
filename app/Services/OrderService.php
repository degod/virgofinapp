<?php

namespace App\Services;

use App\Enums\OrderSideEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Asset\AssetRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private AssetRepositoryInterface $assetRepository
    ) {}

    public function createOrder(User $user, array $data): Order
    {
        $symbol = $data['symbol'];
        $side   = $data['side'];
        $price  = (float) $data['price'];
        $amount = (float) $amount = $data['amount'];

        return DB::transaction(function () use ($user, $symbol, $side, $price, $amount) {
            // Lock the user row to prevent race conditions on balance
            $user = $user->where('id', $user->id)->lockForUpdate()->first(); //$user->freshLock();

            if ($side === OrderSideEnum::BUY) {
                $totalUsd = $price * $amount;

                if ($user->balance < $totalUsd) {
                    throw new Exception('Insufficient USD balance');
                }

                $user->balance -= $totalUsd;
                $user->save();
            } else { // SELL
                $asset = $this->assetRepository->findByUserAndSymbol($user->id, $symbol, lock: true);

                if (!$asset) {
                    $asset = $this->assetRepository->create([
                        'user_id' => $user->id,
                        'symbol' => $symbol,
                        'amount' => 0,
                        'locked_amount' => 0,
                    ]);
                }

                if ($asset->amount < $amount) {
                    throw new Exception('Insufficient asset balance');
                }

                $asset->amount -= $amount;
                $asset->locked_amount += $amount;
                $this->assetRepository->update($asset, [
                    'amount' => $asset->amount,
                    'locked_amount' => $asset->locked_amount,
                ]);
            }

            // Create the order
            return $this->orderRepository->create([
                'user_id' => $user->id,
                'symbol' => $symbol,
                'side' => $side,
                'price' => $price,
                'amount' => $amount,
                'status' => OrderStatusEnum::OPEN,
            ]);
        });
    }

    public function cancelOrder(Order $order): void
    {
        if ($order->status !== OrderStatusEnum::OPEN) {
            throw new Exception('Only open orders can be cancelled.');
        }

        if ($order->side === OrderSideEnum::BUY) {
            // Refund locked USD
            $refund = $order->price * $order->amount;
            $user = $order->user;
            $user->balance += $refund;
            $user->save();
        } else {
            // SELL → refund locked asset
            $asset = $this->assetRepository->findByUserAndSymbol($order->user_id, $order->symbol);

            if ($asset) {
                $asset->locked_amount -= $order->amount;
                $asset->amount += $order->amount;
                $asset->save();
            }
        }

        $order->status = OrderStatusEnum::CANCELLED;
        $order->save();
    }

    public function getUserAssetState(User $user, string $symbol): array
    {
        $asset = $this->assetRepository->findByUserAndSymbol($user->id, $symbol);

        if (! $asset) {
            return [
                'symbol'        => $symbol,
                'amount'        => 0.0,
                'locked_amount' => 0.0,
                'total'         => 0.0,
            ];
        }

        return [
            'symbol'        => $asset->symbol,
            'amount'        => (float) $asset->amount,
            'locked_amount' => (float) $asset->locked_amount,
            'total'         => (float) ($asset->amount + $asset->locked_amount),
        ];
    }
}
