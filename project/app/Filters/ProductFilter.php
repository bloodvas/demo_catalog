<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Group;

class ProductFilter extends Filter
{
    /**
     * Фильтр по названию товара (LIKE)
     *
     * @param string $value
     * @return Builder
     */
    protected function name(string $value): Builder
    {
        return $this->builder->where('products.name', 'like', '%' . $value . '%');
    }

    /**
     * Фильтр по ID группы (включая все подгруппы)
     *
     * @param int $value
     * @return Builder
     */
    protected function group_id(int $value): Builder
    {
        return $this->builder->whereIn('products.id_group', Group::getAllSubGroupIdsRecursive($value));
    }

    /**
     * Фильтр по цене (min/max)
     *
     * @param array $value ['min' => float, 'max' => float]
     * @return Builder
     */
    protected function price(array $value): Builder
    {
        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;

        if ($min !== null && $max !== null) {
            return $this->builder->whereBetween('prices.price', [(float)$min, (float)$max]);
        }

        if ($min !== null) {
            return $this->builder->where('prices.price', '>=', (float)$min);
        }

        if ($max !== null) {
            return $this->builder->where('prices.price', '<=', (float)$max);
        }

        return $this->builder;
    }
    /**
     * Сортировка order(asc/desc) по sort полям (name/price/created_at)
     *
     * @param array $value ['name' => 'asc']
     * @return Builder
     */
    protected function sort(array $value): Builder
    {
        $field = $value['field'] ?? 'price';
        $direction = $value['direction'] ?? 'desc';

        $orderBy = match ($field) {
            'name' => "LOWER(products.name) {$direction}",
            'price' => "COALESCE(prices.price, 0) {$direction}",
            default => "products.created_at {$direction}",
        };

        return $this->builder->orderByRaw($orderBy);
    }
}
