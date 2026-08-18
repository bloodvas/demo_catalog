<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
        use App\Models\Product;
        use App\Http\Requests\ProductFilterRequest;
        use App\Filters\ProductFilter;




class CatalogController extends Controller
{
    /**
     * Получить все группы первого уровня (id_parent = 0)
     * Для каждой группы считаем ВСЕ товары включая подгруппы
     */
    public function groups()
    {
        $groups = Group::where('id_parent', 0)
            ->with('children')
            ->get()
            ->map(function(Group $group){
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'parent_id' => $group->parent_id,
                    'product_count' => $group->getAllProductsCount(),
                    'children' => $group->children->map(function (Group $child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'parent_id' => $child->id_parent,
                            'product_count' => $child->getAllProductsCount(),
                        ];
                    }),
                ];
        });

        return response()->json($groups);
    }

    /**
     * Получить дерево групп для конкретной группы
     * Например, если выбран "Электроника", вернём только её подгруппы
     */

    public function groupTree(Group $group) : JsonResponse
    {
        $tree = $this->buildGroupTree($group);

        return response()->json($tree);
    }

    private function buildGroupTree(Group $group) : array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'parent_id' => $group->id_parent,
            'product_count' => $group->getAllProductsCount(),
            'children' => $group->children->map(function (Group $child) {
                return $this->buildGroupTree($child);
            })->toArray(),
        ];
    }

    /**
     * Получить товары в группе и её подгруппах
     *
     * GET /api/group/{id}/products
     *
     * @param int $id
     * @return JsonResponse
     */
    public function groupProducts(Group $group) : JsonResponse
    {
        $all_group_ids = $group->getAllSubGroupIds();
        $products = Product::whereIn('id_group', $all_group_ids)
            ->get()
            ->toArray();

        return response()->json($products);
    }
    /**
     * Получить товары с пагинацией и сортировкой.
     *
     * GET /api/products?group_id=X&sort=price&order=desc&per_page=12&page=1
     *
     * @return JsonResponse
     */
    public function products(ProductFilterRequest $req, ProductFilter $filter) : JsonResponse
    {
        $perPage = $req->input('per_page',12);

        $products = Product::with(['price', 'group'])
            ->filter($filter)
            ->paginate($perPage);

        return response()->json($products);
    }

}
