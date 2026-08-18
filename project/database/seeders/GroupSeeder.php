<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    // Категории первого уровня для осмысленных подкатегорий
    private const TOP_LEVEL_CATEGORIES = [
        'Электроника' => [
            1 => ['Смартфоны', 'Ноутбуки', 'Планшеты', 'Наушники', 'Телевизоры'],
            2 => ['Android', 'iOS', 'Windows', 'macOS', 'Linux'],
            3 => ['Бюджетные', 'Средние', 'Премиум', 'Флагманы'],
        ],
        'Одежда' => [
            1 => ['Мужская', 'Женская', 'Детская', 'Обувь', 'Аксессуары'],
            2 => ['Летняя', 'Зимняя', 'Демисезон', 'Спортивная', 'Повседневная'],
            3 => ['Базовая', 'Дизайнерская', 'Коллекция X', 'Лимитка'],
        ],
        'Дом и сад' => [
            1 => ['Мебель', 'Декор', 'Сад', 'Инструменты', 'Текстиль'],
            2 => ['Гостиная', 'Спальня', 'Кухня', 'Ванная', 'Офис'],
            3 => ['Классика', 'Модерн', 'Сканди', 'Лофт'],
        ],
        'Спорт' => [
            1 => ['Фитнес', 'Бег', 'Плавание', 'Велоспорт', 'Единоборства'],
            2 => ['Для дома', 'Для зала', 'Для улицы', 'Путешествия'],
            3 => ['Начинающий', 'Продвинутый', 'Профессиональный'],
        ],
        'Книги' => [
            1 => ['Художественная', 'Научная', 'Учебная', 'Детские', 'Комиксы'],
            2 => ['Русская', 'Иностранная', 'Классика', 'Современная', 'Фантастика'],
            3 => ['Бестселлер', 'Книга месяца', 'Выбор редакции'],
        ],
    ];

    public function run(): void
    {
        // Создаём 10 групп первого уровня (id_parent = 0)
        foreach (self::TOP_LEVEL_CATEGORIES as $name => $subCategories) {
            $group = Group::create([
                'name' => $name,
                'id_parent' => 0,
            ]);

            // Рекурсивно создаём подглубину максимум 3 уровня
            $this->createChildren($group, 1, $subCategories);
        }
    }

    /**
     * Рекурсивно создаёт дочерние группы.
     *
     * @param Group $parent   Родительская группа
     * @param int   $level    Текущий уровень (1 = первый уровень детей)
     * @param array $categories Ограничение названий по уровням
     */
    private function createChildren(Group $parent, int $level, array $categories = []): void
    {
        // Останавливаем рекурсию на 3 уровне — больше не нужно для каталога
        if ($level > 3) {
            return;
        }

        // Вероятность 30%, что у группы нет детей (листья дерева)
        if (rand(0, 2) === 0) {
            return;
        }

        // Берём варианты названий для текущего уровня
        $names = $categories[$level] ?? [];

        // Если нет predefined названий — используем Faker
        if (empty($names)) {
            $childCount = rand(1, 3);
            for ($i = 0; $i < $childCount; $i++) {
                Group::create([
                    'name' => fake()->word(),
                    'id_parent' => $parent->id,
                ]);
            }
            return;
        }

        // Создаём 1-3 дочерние группы из predefined названий
        $childCount = rand(1, min(3, count($names)));
        $selectedNames = array_rand($names, $childCount);

        // Если выбрано только одно, array_rand вернёт ключ, а не массив
        if (!is_array($selectedNames)) {
            $selectedNames = [$selectedNames];
        }

        foreach ($selectedNames as $key) {
            $child = Group::create([
                'name' => $names[$key],
                'id_parent' => $parent->id,
            ]);

            // Рекурсия: создаём детей для этого ребёнка с next уровнем
            $this->createChildren($child, $level + 1, $categories);
        }
    }
}
