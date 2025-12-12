<?php

namespace Database\Factories;

use App\AssetEnum;
use App\Models\Order;
use App\Models\User;
use App\OrderSideEnum;
use App\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        return [
            'user_id' => $user->id,
            'symbol' => $this->faker->randomElement(AssetEnum::symbols()),
            'side' => $this->faker->randomElement(OrderSideEnum::getSides()),
            'price' => $this->faker->randomFloat(2, 10, 100000),
            'amount' => $this->faker->randomFloat(8, 0.01, 5),
            'status' => $this->faker->randomElement(OrderStatusEnum::getStatuses()),
        ];
    }
}
