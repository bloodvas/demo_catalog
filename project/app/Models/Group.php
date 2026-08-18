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

    public function products() : HasMany
    {
        return $this->hasMany(Product::class, 'id_group');
    }

    public function children() : HasMany
    {
        return $this->hasMany(Group::class, 'id_parent');
    }

    public function parent() : BelongsTo
    {
        return $this->belongsTo(Group::class, 'id_parent');
    }

    public function getAllSubGroupIds() : array
    {
        $query = "
        with recursive subgroups as (
            select id from groups where id = ?
            union all
            select g.id from groups as g
            inner join subgroups as s on g.id_parent = s.id
        )
        select id from subgroups
    ";

        // DB::select() — выполняет сырой SQL и возвращает массив объектов
        $results = DB::select($query, [$this->id]);

        // Преобразуем массив объектов [{id: 1}, {id: 2}] в простой массив [1, 2]
        return array_map(fn($r) => $r->id, $results);
    }

    public function getAllProductsCount() : int
    {
        $subGroupIds = $this->getAllSubGroupIds();

        if (empty($subGroupIds)) {
            return 0;
        }

        return DB::table('products')
            ->whereIn('id_group', $subGroupIds)
            ->count();
    }
}
