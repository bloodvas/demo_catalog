<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_product',
        'price',
    ];

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
}
