<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\StockLevel;
use App\Jobs\SendLowStockNotification;
use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product): void
    {
        if ($product->stock_quantity <= 0) {
            $product->status = \App\Enums\ProductStatus::Inactive;
        }
    }

    public function updated(Product $product): void
    {
        if ($product->stock_quantity < 5 && $product->stock_quantity > 0 && $product->wasChanged('stock_quantity')) {
            SendLowStockNotification::dispatch($product);
        }
    }
}
