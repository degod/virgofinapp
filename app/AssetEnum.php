<?php

namespace App;

enum AssetEnum
{
    case BTC;
    case ETH;
    case USDT;
    case BNB;

    public static function symbols(): array
    {
        return array_map(fn(self $asset) => $asset->name, self::cases());
    }
}
