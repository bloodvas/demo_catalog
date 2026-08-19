<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Product;
use App\Http\Requests\ProductFilterRequest;
use App\Filters\ProductFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * Получить полное дерево групп (рекурсивно, без ограничений глубины)
     * GET /api/groups
     */
    public function groups(): JsonResponse
    {
        $groups = Group::where('id_parent', 0)
            ->get()
            ->map(fn(Group $group) => $group->buildFullTree());

        return response()->json($groups);
    }

    /**
     * Хлебные крошки для группы
     * GET /api/groups/{id}/breadcrumbs
     */
    public function groupBreadcrumbs(int $id): JsonResponse
    {
        $group = Group::with('parent')
            ->where('id', $id)
            ->first();

        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        $breadcrumbs = [];
        $current = $group;

        while ($current) {
            array_unshift($breadcrumbs, [
                'id' => $current->id,
                'name' => $current->name,
            ]);
            $current = $current->parent;
        }

        return response()->json($breadcrumbs);
    }

    /**
     * Фильтрованные и отсортированные товары с пагинацией
     * POST /api/products
     */
    public function products(ProductFilterRequest $request, ProductFilter $filter): JsonResponse
    {
        $validated = $request->validated();

        $per_page = $validated['per_page'] ?? 12;
        $page = $validated['page'] ?? 1;
        $sort = $validated['sort'] ?? 'price';
        $order = $validated['order'] ?? 'desc';

        $products = Product::with(['price', 'group'])
            ->join('prices', 'products.id', '=', 'prices.id_product')
            ->when($validated['group_id'] ?? null, fn($q, $g) => $q->whereIn('products.id_group', (new Group())->getAllSubGroupIdsRecursive($g)))
            ->filter($filter)
            ->orderByRaw(match ($sort) {
                'name' => "LOWER(products.name) {$order}",
                'price' => "COALESCE(prices.price, 0) {$order}",
                default => "products.created_at {$order}",
            })
            ->paginate($per_page, ['products.*'], 'page', $page);

        return response()->json($products);
    }

    /**
     * Хлебные крошки для товара
     * GET /api/products/{id}/breadcrumbs
     */
    public function productBreadcrumbs(int $id): JsonResponse
    {
        $product = Product::with('group.parent')
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $breadcrumbs = [];
        $group = $product->group;

        // Собираем группы от корня к товару
        $groupPath = [];
        $current = $group;
        while ($current) {
            array_unshift($groupPath, $current);
            $current = $current->parent;
        }

        foreach ($groupPath as $g) {
            $breadcrumbs[] = [
                'type' => 'group',
                'id' => $g->id,
                'name' => $g->name,
            ];
        }

        $breadcrumbs[] = [
            'type' => 'product',
            'id' => $product->id,
            'name' => $product->name,
        ];

        return response()->json($breadcrumbs);
    }

    /**
     * Детальная информация о товаре
     * GET /api/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['price', 'group.parent.children'])
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $breadcrumbs = [];
        $groupPath = [];
        $current = $product->group;
        while ($current) {
            array_unshift($groupPath, $current);
            $current = $current->parent;
        }

        foreach ($groupPath as $g) {
            $breadcrumbs[] = [
                'type' => 'group',
                'id' => $g->id,
                'name' => $g->name,
            ];
        }
        $breadcrumbs[] = [
            'type' => 'product',
            'id' => $product->id,
            'name' => $product->name,
        ];

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price->price ?? null,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
