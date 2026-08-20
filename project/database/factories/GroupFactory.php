<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    /**
     * Определение состояния по умолчанию фабрики.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_parent' => 0,
            'name' => fake()->word(),
        ];
    }

    /**
     * Создать группу первого уровня.
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_parent' => 0,
        ]);
    }

    /**
     * Создать вложенную группу.
     */
    public function child(int $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'id_parent' => $parentId,
        ]);
    }
}
