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

    // Keep track of user symbols in memory during factory execution
    private static array $userAssignedSymbols = [];

    public function definition(): array
    {
        // Pick a user who has less than all symbols assigned
        $user = User::inRandomOrder()->get()->first(function (User $u) {
            $assigned = self::$userAssignedSymbols[$u->id] ?? [];
            return count($assigned) < count(\App\AssetEnum::symbols());
        });

        // If no suitable user, create a new one
        if (! $user) {
            $user = User::factory()->create();
        }

        $userId = $user->id;

        // Get assigned symbols for this user
        $assignedSymbols = self::$userAssignedSymbols[$userId] ?? [];

        // Pick a symbol not assigned yet
        $availableSymbols = array_diff(\App\AssetEnum::symbols(), $assignedSymbols);
        $symbol = $this->faker->randomElement($availableSymbols);

        // Update the in-memory tracker
        self::$userAssignedSymbols[$userId][] = $symbol;

        return [
            'user_id' => $userId,
            'symbol' => $symbol,
            'amount' => $this->faker->randomFloat(8, 0, 10),
            'locked_amount' => $this->faker->randomFloat(8, 0, 5),
        ];
    }
}
