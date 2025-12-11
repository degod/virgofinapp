<?php

namespace App\Repositories\Asset;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;

interface AssetRepositoryInterface
{
    public function findByUserAndSymbol(int $userId, string $symbol): ?Asset;
    public function create(array $data): Asset;
    public function update(Asset $asset, array $data): bool;
    public function getUserAssets(int $userId): Collection;
}
