<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use App\Filters\ProductFilter;


class CatalogController extends Controller
{
    public function __construct(
        private CatalogService $catalogService
    ) {}

    public function groups(): JsonResponse
    {
        return response()->json($this->catalogService->getGroupTree());
    }

    public function groupBreadcrumbs(int $id): JsonResponse
    {
        $breadcrumbs = $this->catalogService->getGroupBreadcrumbs($id);

        if (empty($breadcrumbs)) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        return response()->json($breadcrumbs);
    }

    public function products(ProductFilterRequest $request, ProductFilter $filter): JsonResponse
    {
        return response()->json($this->catalogService->getProducts($request, $filter));
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }
}
