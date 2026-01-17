<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function listActivePaginated(int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->where('status', ProductStatus::Active)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function listAll(): Collection
    {
        return Product::query()
            ->orderBy('name')
            ->get();
    }

    /** @param array{name:string,description:?string,price:string,stock_quantity:int,status:ProductStatus,image_path:?string} $data */
    public function create(User $creator, array $data): Product
    {
        return Product::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'],
            'status' => $data['status'],
            'image_path' => $data['image_path'],
            'created_by' => $creator->id,
        ]);
    }

    /** @param array{name:string,description:?string,price:string,stock_quantity:int,status:ProductStatus,image_path:?string} $data */
    public function update(Product $product, array $data): Product
    {
        $product->fill([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'],
            'status' => $data['status'],
            'image_path' => $data['image_path'],
        ]);

        $product->save();

        return $product;
    }
}
