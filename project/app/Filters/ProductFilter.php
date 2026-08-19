<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Group;

class ProductFilter extends Filter
{
    /**
     * Фильтрация по названию
     */
    protected function name(string $value): Builder
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Фильтрация по ID группы
     */
    protected function group_id(int $value): Builder
    {
        return $this->builder->whereIn('id_group', Group::getAllSubGroupIdsRecursive($value));
    }

    /**
     * Фильтрация по цене (диапазон)
     */
    protected function price(array $value): Builder
    {
        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;

        if ($min !== null && $max !== null) {
            return $this->builder->whereBetween('price.price', [(float)$min, (float)$max]);
        }

        if ($min !== null) {
            return $this->builder->where('price.price', '>=', (float)$min);
        }

        if ($max !== null) {
            return $this->builder->where('price.price', '<=', (float)$max);
        }

        return $this->builder;
    }

    /**
     * Сортировка: сортировка передается через запрос
     * В нашем случае сортировка обрабатывается в контроллере,
     * но можно добавить и сюда
     */
    protected function sort(string $value): Builder
    {
        return $this->builder;
    }

    protected function order(string $value): Builder
    {
        return $this->builder;
    }
}
