<?php

declare(strict_types=1);

namespace App\Enums;

enum StockLevel: string
{
    case Normal = 'normal';
    case Low = 'low';
    case OutOfStock = 'out_of_stock';
}
