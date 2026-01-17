<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductStoreRequest;
use App\Http\Requests\Admin\ProductUpdateRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(): Response
    {
        $products = $this->productService->listAll();

        return Inertia::render('admin/products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Create', [
            'statuses' => array_column(ProductStatus::cases(), 'name'),
        ]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        $data['status'] = ProductStatus::from($data['status']);

        $this->productService->create($user, $data);

        return to_route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('admin/products/Edit', [
            'product' => $product,
            'statuses' => array_column(ProductStatus::cases(), 'name'),
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $data['status'] = ProductStatus::from($data['status']);

        $this->productService->update($product, $data);

        return to_route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return to_route('admin.products.index')->with('success', 'Product deleted.');
    }
}
