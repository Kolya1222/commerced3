<?php

namespace roilafx\Commerced3\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'site_content';

    protected $casts = [
        'id'     => 'integer',
        'parent' => 'integer',
    ];

    /**
     * Родительская категория (папка).
     */
    public function category()
    {
        return $this->belongsTo(Product::class, 'parent');
    }
}