<?php

namespace roilafx\Commerced3\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $table = 'commerce_order_products';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'title',
        'price',
        'count',
        'options',
        'meta',
        'position',
    ];

    protected $casts = [
        'order_id'   => 'integer',
        'product_id' => 'integer',
        'price'      => 'float',
        'count'      => 'float',
        'position'   => 'integer',
    ];

    /**
     * Заказ, к которому относится позиция.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Товар-ресурс Evolution CMS.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}