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

    /**
     * Связь: товары в этой группе
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_group');
    }

    /**
     * Связь: дочерние группы
     *
     * @return HasMany<Group>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Group::class, 'id_parent');
    }

    /**
     * Связь: родительская группа
     *
     * @return BelongsTo<Group>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'id_parent');
    }

    /**
     * Получить все подгруппы (рекурсивно) через CTE
     *
     * @return int[]
     */
    public function getAllSubGroupIds(): array
    {
        return $this->getSubGroupIdsFor($this->id);
    }

    /**
     * Получить все подгруппы для указанной группы (статический метод)
     *
     * @param int $groupId
     * @return int[]
     */
    public static function getAllSubGroupIdsRecursive(int $groupId): array
    {
        return (new static())->getSubGroupIdsFor($groupId);
    }

    /**
     * Рекурсивный SQL CTE для получения ID подгрупп
     *
     * @param int $groupId
     * @return int[]
     */
    private function getSubGroupIdsFor(int $groupId): array
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

        return array_map(fn($r) => (int) $r->id, $results);
    }
}
