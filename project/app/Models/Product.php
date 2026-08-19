<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasFilter;

class Product extends Model
{
    use HasFactory, HasFilter;

    protected $fillable = [
        'id_group',
        'name',
    ];

    /**
     * Связь: цена товара (один к одному)
     *
     * @return HasOne<Price>
     */
    public function price(): HasOne
    {
        return $this->hasOne(Price::class, 'id_product');
    }

    /**
     * Связь: группа товара
     *
     * @return BelongsTo<Group>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'id_group');
    }
}
