<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Определение состояния по умолчанию фабрики.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_group' => \App\Models\Group::factory(),
            'name' => fake()->words(3, true),
        ];
    }
}
