<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Product;
use App\Filters\ProductFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\ProductFilterRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Сервис для работы с каталогом товаров.
 *
 * Методы:
 * - getGroupTree() -> array
 * - getProduct($id) -> array|false
 * - getProducts($params) -> array (paginated)
 * - getBreadcrumbs($id, $type) -> array
 */
class CatalogService
{
    /**
     * Получить полное дерево групп первого уровня (без ограничений глубины)
     *
     * @return array
     */
    public function getGroupTree(): array
    {
        return Group::where('id_parent', 0)
            ->get()
            ->map(fn(Group $group) => $this->buildGroupNode($group))
            ->toArray();
    }

    /**
     * Рекурсивно собрать узел дерева групп
     *
     * @param Group $group
     * @return array
     */
    private function buildGroupNode(Group $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'product_count' => $this->getGroupProductCount($group),
            'children' => $group->children->map(
                fn(Group $child) => $this->buildGroupNode($child)
            )->toArray(),
        ];
    }

    /**
     * Получить количество товаров в группе и всех её подгруппах
     *
     * @param Group $group
     * @return int
     */
    private function getGroupProductCount(Group $group): int
    {
        $subGroupIds = $group->getAllSubGroupIds();

        if (empty($subGroupIds)) {
            return 0;
        }

        return DB::table('products')
            ->whereIn('id_group', $subGroupIds)
            ->count();
    }

    /**
     * Получить детальную информацию о товаре с хлебными крошками
     *
     * @param int $id
     * @return array|false
     */
    public function getProduct(int $id): array|false
    {
        $product = Product::with(['price', 'group.parent'])->find($id);

        if (!$product) {
            return false;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price?->price ?? null,
            'breadcrumbs' => $this->getProductBreadcrumbs($product),
        ];
    }

    /**
     * Получить товары с фильтрацией, сортировкой и пагинацией
     *
     * @param array $params  ['group_id', 'sort', 'order', 'name', 'price.min', 'price.max', 'page', 'per_page']
     * @return array
     */
    public function getProducts(ProductFilterRequest $req, ProductFilter $filter): LengthAwarePaginator
    {
        $paginator = Product::select('products.*')
            ->leftJoin('prices', 'products.id', '=', 'prices.id_product')
            ->with(['price', 'group'])
            ->filter($filter);

        return $paginator->paginate(
            perPage: $req->integer('per_page', 12),
            columns: ['products.*'],
            pageName: 'page',
            page: $req->integer('page', 1)
        );
    }

    /**
     * Получить хлебные крошки для группы
     *
     * @param int $id
     * @return array
     */
    public function getGroupBreadcrumbs(int $id): array
    {
        $group = Group::with('parent')->find($id);

        if (!$group) {
            return [];
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

        return $breadcrumbs;
    }

    /**
     * Получить хлебные крошки для товара
     *
     * @param Product $product
     * @return array
     */
    private function getProductBreadcrumbs(Product $product): array
    {
        $breadcrumbs = [];
        $group = $product->group;

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

        return $breadcrumbs;
    }
}
