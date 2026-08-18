<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Создаём товары для каждой группы.
     * Каждая группа получит от 3 до 8 товаров.
     */
    public function run(): void
    {
        // Получаем все группы, которые ещё не имеют товаров
        // (чтобы не дублировать при повторном запуске)
        $groups = Group::doesntHave('products')->get();

        foreach ($groups as $group) {
            $count = rand(3, 8);

            for ($i = 0; $i < $count; $i++) {
                Product::create([
                    'id_group' => $group->id,
                    'name' => fake()->words(rand(2, 4), true),
                ]);
            }
        }
    }
}
