<?php

namespace App\Enums;

enum OrderStatusEnum
{
    const OPEN = 1;
    const FILLED = 2;
    const CANCELLED = 3;

    public static function getStatuses(): array
    {
        return [
            self::OPEN,
            self::FILLED,
            self::CANCELLED,
        ];
    }
}
