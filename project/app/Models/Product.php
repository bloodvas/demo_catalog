<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;




class Product extends Model
{
    use HasFactory;

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
}
