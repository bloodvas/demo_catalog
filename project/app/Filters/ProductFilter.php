<?php
declare(strict_types=1);

namespace App\Filters;

use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filter
{
    /**
     * Сортировка по названию
     */
    protected function sort(string $value): Builder
    {
        $order = $this->request->input('order', 'asc');

        return match ($value) {
            'price' => $this->builder->join('prices', 'products.id', '=', 'prices.id_product')
                ->orderBy('prices.price', $order),
            'name' => $this->builder->orderBy('products.name', $order),
            default => $this->builder,
        };
    }

    /**
     * Фильтрация по группе
     */
    protected function groupId(int $value): Builder
    {
        // Получаем все подгруппы
        $groupIds = app(Group::class)->find($value)->getAllSubGroupIds();

        return $this->builder->whereIn('id_group', $groupIds);
    }

    /**
     * Фильтрация по названию (like search)
     */
    protected function name(string $value): Builder
    {
        return $this->builder->where('products.name', 'like', '%' . $value . '%');
    }

    /**
     * Фильтрация по минимальной цене
     */
    protected function priceMin(float $value): Builder
    {
        return $this->builder->join('prices', 'products.id', '=', 'prices.id_product')
            ->where('prices.price', '>=', $value);
    }

    /**
     * Фильтрация по максимальной цене
     */
    protected function priceMax(float $value): Builder
    {
        return $this->builder->join('prices', 'products.id', '=', 'prices.id_product')
            ->where('prices.price', '<=', $value);
    }
}
