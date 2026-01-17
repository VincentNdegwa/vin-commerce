<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Product;
use App\Notifications\LowStockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLowStockNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Product $product)
    {
    }

    public function handle(): void
    {
        $product = $this->product->loadMissing('creator');

        $creator = $product->creator;

        if ($creator === null) {
            return;
        }

        $creator->notify(new LowStockNotification($product));
    }
}
