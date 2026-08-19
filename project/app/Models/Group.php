<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_parent',
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_group');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Group::class, 'id_parent');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'id_parent');
    }

    /**
     * Получить все подгруппы (рекурсивно) через CTE
     */
    public function getAllSubGroupIds(): array
    {
        $query = "
            WITH RECURSIVE subgroups AS (
                SELECT id FROM groups WHERE id = ?
                UNION ALL
                SELECT g.id FROM groups g
                INNER JOIN subgroups s ON g.id_parent = s.id
            )
            SELECT id FROM subgroups
        ";

        $results = DB::select($query, [$this->id]);

        return array_map(fn($r) => $r->id, $results);
    }

    /**
     * Посчитать количество товаров во всех подгруппах
     */
    public function getAllProductsCount(): int
    {
        $subGroupIds = $this->getAllSubGroupIds();

        if (empty($subGroupIds)) {
            return 0;
        }

        return DB::table('products')
            ->whereIn('id_group', $subGroupIds)
            ->count();
    }

    /**
     * Получить все подгруппы для указанной группы (статический метод)
     */
    public static function getAllSubGroupIdsRecursive(int $groupId): array
    {
        $query = "
            WITH RECURSIVE subgroups AS (
                SELECT id FROM groups WHERE id = ?
                UNION ALL
                SELECT g.id FROM groups g
                INNER JOIN subgroups s ON g.id_parent = s.id
            )
            SELECT id FROM subgroups
        ";

        $results = DB::select($query, [$groupId]);

        return array_map(fn($r) => $r->id, $results);
    }

    /**
     * Рекурсивно строит полное дерево групп без ограничений глубины
     * Использует loadMissing для ленивой загрузки children
     */
    public function buildFullTree(): array
    {
        // Загружаем children только если ещё не загружены
        $this->loadMissing('children');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'product_count' => $this->getAllProductsCount(),
            'children' => $this->children->map(fn($child) => $child->buildFullTree())->toArray(),
        ];
    }

    /**
     * Получить полный breadcrumb путь от корня до текущей группы
     */
    public function getBreadcrumbPath(): array
    {
        $breadcrumbs = [];
        $current = $this;

        while ($current) {
            array_unshift($breadcrumbs, [
                'id' => $current->id,
                'name' => $current->name,
            ]);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }
}
