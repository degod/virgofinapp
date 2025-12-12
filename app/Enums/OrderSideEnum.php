<?php

namespace App\Enums;

enum OrderSideEnum
{
    const BUY = 'buy';
    const SELL = 'sell';

    public static function getSides(): array
    {
        return [
            self::BUY,
            self::SELL,
        ];
    }
}
