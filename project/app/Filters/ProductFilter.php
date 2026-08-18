<?php
declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Group;

class ProductFilter extends Filter
{
    /**
     * Фильтрация по названию
     *
     * @param string $value
     * @return Builder
     */
    protected function name(string $value): Builder
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    /**
     * Фильтрация по категории
     *
     * @param string $value
     * @return Builder
     */
    protected function group(int $value): Builder
    {
        $groups_ids = Group::findOrFail($value)->getAllSubGroupIds();
        return $this->builder->whereIn('id_group', $groups_ids);
    }

    /**
     * Фильтрация по цене
     *
     * @param array $value
     * @return Builder
     */
    protected function price(array $value): Builder
    {
        return $this->builder->whereBetween('price', [$value['min'], $value['max']]);
    }
}
