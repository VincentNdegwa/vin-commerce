<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ProductStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $statusValues = array_map(fn (ProductStatus $status): string => $status->value, ProductStatus::cases());

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:'.implode(',', $statusValues)],
            'image_path' => ['nullable', 'string'],
        ];
    }
}
