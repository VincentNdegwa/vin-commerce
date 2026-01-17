@component('mail::message')
# Low stock alert

The following product is low in stock:

- Name: {{ $product->name }}
- Current stock: {{ $product->stock_quantity }}

Please review and restock as needed.

@endcomponent
