<?php

namespace App\Repositories\Asset;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Collection;

class AssetRepository implements AssetRepositoryInterface
{
    public function __construct(private Asset $model)
    {
        $this->model = $model;
    }

    public function findByUserAndSymbol(int $userId, string $symbol, bool $lock = false): ?Asset
    {
        $query = Asset::where('user_id', $userId)->where('symbol', $symbol);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function create(array $data): Asset
    {
        return $this->model->create($data);
    }

    public function update(Asset $asset, array $data): bool
    {
        return $asset->update($data);
    }

    public function getUserAssets(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }
}
