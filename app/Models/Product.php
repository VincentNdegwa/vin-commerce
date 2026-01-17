<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'status',
        'image_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => ProductStatus::class,
        ];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
