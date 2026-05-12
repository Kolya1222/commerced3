<?php

namespace roilafx\Commerced3\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = 'commerce_order_payments';

    protected $fillable = [
        'order_id',
        'amount',
        'paid',
        'hash',
        'payment_method',
        'original_order_id',
        'meta',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'amount'   => 'float',
        'paid'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Заказ, к которому привязан платёж.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}