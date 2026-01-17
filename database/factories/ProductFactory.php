<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Product>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Wireless Noise-Cancelling Headphones',
            '4K Ultra HD Smart TV',
            'Ergonomic Office Chair',
            'Mechanical Gaming Keyboard',
            'USB-C Portable SSD 1TB',
            'Bluetooth Speaker',
            'Stainless Steel Water Bottle',
            'Fitness Tracker Watch',
            'Laptop Backpack',
            'Smartphone Tripod Stand',
        ];

        $creatorId = $this->resolveCreatorId();

        return [
            'name' => fake()->randomElement($names),
            'description' => fake()->sentence(12),
            'price' => fake()->randomFloat(2, 5, 500),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'status' => ProductStatus::Active,
            'image_path' => null,
            'created_by' => $creatorId,
        ];
    }

    private function resolveCreatorId(): int
    {
        $admin = User::query()
            ->where('role', UserRole::Admin)
            ->orderBy('id')
            ->first();

        if ($admin !== null) {
            return $admin->id;
        }

        $user = User::query()->orderBy('id')->first();

        if ($user !== null) {
            return $user->id;
        }

        $newAdmin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        return $newAdmin->id;
    }
}
