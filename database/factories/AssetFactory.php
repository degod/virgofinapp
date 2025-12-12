<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    private static array $userAssignedSymbols = [];

    public function definition(): array
    {
        $user = User::inRandomOrder()->get()->first(function (User $u) {
            $assigned = self::$userAssignedSymbols[$u->id] ?? [];
            return count($assigned) < count(\App\AssetEnum::symbols());
        });
        if (! $user) {
            $user = User::factory()->create();
        }

        $userId = $user->id;
        $assignedSymbols = self::$userAssignedSymbols[$userId] ?? [];

        $availableSymbols = array_diff(\App\AssetEnum::symbols(), $assignedSymbols);
        $symbol = $this->faker->randomElement($availableSymbols);
        self::$userAssignedSymbols[$userId][] = $symbol;

        return [
            'user_id' => $userId,
            'symbol' => $symbol,
            'amount' => $this->faker->randomFloat(8, 0, 10),
            'locked_amount' => $this->faker->randomFloat(8, 0, 5),
        ];
    }
}
