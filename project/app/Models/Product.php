<?php

namespace App\Models;

use App\Filters\ProductFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasFilter;




class Product extends Model
{
    use HasFactory, HasFilter;

    protected $fillable = [
        'id_group',
        'name',
    ];

    public function price() : HasOne
    {
        return $this->hasOne(Price::class, 'id_product');
    }

    public function group() : BelongsTo
    {
        return $this->belongsTo(Group::class, 'id_group');
    }

    /**
     * Scope для применения фильтров
     */
    public function scopeFilter(Builder $builder, ProductFilter $filter): Builder
    {
        return $filter->apply($builder);
    }
}
