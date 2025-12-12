<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $buyer,
        public User $seller,
        public Order $buyOrder,
        public Order $sellOrder,
        public float $amount,
        public float $price,
        public float $usdVolume,
        public float $commission
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->buyer->id),
            new PrivateChannel('user.' . $this->seller->id),
        ];
    }

    public function broadcastAs()
    {
        return 'OrderMatched';
    }

    public function broadcastWith(): array
    {
        return [
            // Payload for the buyer ONLY
            'user.' . $this->buyer->id => [
                'message' => 'Your order has been matched!',
                'trade' => [
                    'amount' => $this->amount,
                    'price' => $this->price,
                    'usd_volume' => $this->usdVolume,
                    'commission' => $this->commission,
                    'symbol' => $this->buyOrder->symbol,
                    'role' => 'buyer',
                ],
                'wallet' => [
                    'usd_balance' => (float) $this->buyer->balance,
                ],
            ],

            // Payload for the seller ONLY
            'user.' . $this->seller->id => [
                'message' => 'Your order has been matched!',
                'trade' => [
                    'amount' => $this->amount,
                    'price' => $this->price,
                    'usd_volume' => $this->usdVolume,
                    'commission' => $this->commission,
                    'symbol' => $this->buyOrder->symbol,
                    'role' => 'seller',
                ],
                'wallet' => [
                    'usd_balance' => (float) $this->seller->balance,
                ],
            ],
        ];
    }
}
