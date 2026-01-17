<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CartUpdateItemRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
