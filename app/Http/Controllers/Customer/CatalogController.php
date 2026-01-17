<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(): Response
    {
        $products = $this->productService->listActivePaginated();

        return Inertia::render('shop/Index', [
            'products' => $products,
        ]);
    }
}
