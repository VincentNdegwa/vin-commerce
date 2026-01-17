<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\CarbonImmutable;

class SalesReportService
{
    /** @return array{total_sales:string,total_orders:int} */
    public function aggregateForPeriod(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', OrderStatus::Completed)
            ->get();

        $total = $orders->reduce(
            fn (string $carry, Order $order): string => (string) ((float) $carry + (float) $order->total_amount),
            '0',
        );

        return [
            'total_sales' => $total,
            'total_orders' => $orders->count(),
        ];
    }

    /**
     * @return array<int, array{user:User,total_sales:string,total_orders:int}>
     */
    public function aggregateForPeriodByCreator(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', OrderStatus::Completed)
            ->with(['items.product.creator'])
            ->get();

        $result = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $product = $item->product;

                if ($product === null || $product->creator === null) {
                    continue;
                }

                $creator = $product->creator;
                $creatorId = $creator->id;

                if (! array_key_exists($creatorId, $result)) {
                    $result[$creatorId] = [
                        'user' => $creator,
                        'total_sales' => '0',
                        'total_orders' => [],
                        'products' => [],
                    ];
                }

                $subtotal = (float) $item->subtotal;

                $result[$creatorId]['total_sales'] = (string) ((float) $result[$creatorId]['total_sales'] + $subtotal);
                $result[$creatorId]['total_orders'][$order->id] = true;

                 $productId = $product->id;

                 if (! array_key_exists($productId, $result[$creatorId]['products'])) {
                     $result[$creatorId]['products'][$productId] = [
                         'product_id' => $productId,
                         'name' => $product->name,
                         'quantity' => 0,
                         'total_sales' => '0',
                     ];
                 }

                 $result[$creatorId]['products'][$productId]['quantity'] += $item->quantity;
                 $result[$creatorId]['products'][$productId]['total_sales'] = (string) ((float) $result[$creatorId]['products'][$productId]['total_sales'] + $subtotal);
            }
        }

        foreach ($result as $creatorId => $entry) {
            $result[$creatorId]['total_orders'] = count($entry['total_orders']);

            // Re-index products to a simple list
            $result[$creatorId]['products'] = array_values($entry['products']);
        }

        return $result;
    }
}
