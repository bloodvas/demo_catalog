<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    /**
     * Определение состояния по умолчанию фабрики.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_product' => Product::factory(),
            'price' => fake()->randomFloat(2, 100, 50000),
        ];
    }
}
