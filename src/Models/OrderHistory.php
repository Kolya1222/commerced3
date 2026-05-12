<?php

namespace roilafx\Commerced3\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $table = 'commerce_order_history';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'status_id',
        'comment',
        'notify',
        'user_id',
    ];

    protected $casts = [
        'order_id'  => 'integer',
        'status_id' => 'integer',
        'notify'    => 'boolean',
        'user_id'   => 'integer',
        'created_at'=> 'datetime',
    ];

    /**
     * Заказ, историю которого смотрим.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Статус, на который переключили.
     */
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }
}