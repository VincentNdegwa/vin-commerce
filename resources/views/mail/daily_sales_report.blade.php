@component('mail::message')
# {{ $reportName }}

Total orders today: **{{ $data['total_orders'] }}**

Total sales today: **{{ $data['total_sales'] }}**

@if (!empty($data['products']))

@component('mail::table')
| Product | Quantity | Total Sales |
| :------ | -------: | ----------: |
@foreach ($data['products'] as $product)
| {{ $product['name'] }} | {{ $product['quantity'] }} | {{ $product['total_sales'] }} |
@endforeach
@endcomponent

@endif

@endcomponent
