<?php

namespace App\Services;

use App\Enums\OrderSideEnum;
use App\Enums\OrderStatusEnum;
use App\Events\OrderMatched;
use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Asset\AssetRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

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
            $refund = $order->price * $order->amount;
            $user = $order->user;
            $user->balance += $refund;
            $user->save();
        } else {
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

    public function attemptMatch(Order $newOrder): void
    {
        if ($newOrder->status !== OrderStatusEnum::OPEN) {
            Log::info("Order is not open for matching", ['order_id' => $newOrder->id]);
            return;
        }

        try {
            Log::info("Order is processing...", ['order_id' => $newOrder->id]);

            DB::transaction(function () use ($newOrder) {
                $newOrder = $newOrder->where('id', $newOrder->id)->lockForUpdate()->first();

                $oppositeSide = $newOrder->side === OrderSideEnum::BUY ? OrderSideEnum::SELL : OrderSideEnum::BUY;

                $query = Order::where('symbol', $newOrder->symbol)
                    ->where('side', $oppositeSide)
                    ->where('status', OrderStatusEnum::OPEN)
                    ->where('amount', $newOrder->amount)
                    ->lockForUpdate();

                if ($newOrder->side === OrderSideEnum::BUY) {
                    $query->where('price', '<=', $newOrder->price)
                        ->orderBy('price', 'asc')
                        ->orderBy('created_at', 'asc');
                } else {
                    $query->where('price', '>=', $newOrder->price)
                        ->orderBy('price', 'desc')
                        ->orderBy('created_at', 'asc');
                }

                $matchedOrder = $query->first();
                if (!$matchedOrder) {
                    Log::info("No matching order found", [
                        'order_id' => $newOrder->id,
                        'matched_order' => $matchedOrder,
                    ]);
                    return;
                }

                // Match found! Execute trade
                $matchPrice = $matchedOrder->price;
                $usdVolume = $newOrder->amount * $matchPrice;
                $commission = $usdVolume * 0.015;
                $netUsdToSeller = $usdVolume - $commission;

                // Reload users and assets with locks
                $buyer = $newOrder->side === OrderSideEnum::BUY ? $newOrder->user : $matchedOrder->user;
                $seller = $newOrder->side === OrderSideEnum::SELL ? $newOrder->user : $matchedOrder->user;

                $buyer = User::lockForUpdate()->find($buyer->id);
                $seller = User::lockForUpdate()->find($seller->id);
                $assetSymbol = $newOrder->symbol;

                $buyerAsset = Asset::firstOrCreate(
                    ['user_id' => $buyer->id, 'symbol' => $assetSymbol],
                    ['amount' => 0, 'locked_amount' => 0]
                );
                $buyerAsset->amount += $newOrder->amount;
                $buyerAsset->save();

                $sellerAsset = Asset::lockForUpdate()
                    ->where('user_id', $seller->id)
                    ->where('symbol', $assetSymbol)
                    ->firstOrFail();

                $sellerAsset->locked_amount -= $newOrder->amount;
                $sellerAsset->save();

                $seller->balance += $netUsdToSeller;
                $seller->save();

                $newOrder->status = OrderStatusEnum::FILLED;
                $newOrder->save();

                $matchedOrder->status = OrderStatusEnum::FILLED;
                $matchedOrder->save();

                // Broadcast real-time event to both users via Soketi
                broadcast(new OrderMatched(
                    buyer: $buyer,
                    seller: $seller,
                    buyOrder: $newOrder->side === OrderSideEnum::BUY ? $newOrder : $matchedOrder,
                    sellOrder: $newOrder->side === OrderSideEnum::SELL ? $newOrder : $matchedOrder,
                    amount: $newOrder->amount,
                    price: $matchPrice,
                    usdVolume: $usdVolume,
                    commission: $commission
                ));
            });
        } catch (Exception $e) {
            Log::error("Error during order matching", [
                'order_id' => $newOrder->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
