<?php

namespace App;

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
