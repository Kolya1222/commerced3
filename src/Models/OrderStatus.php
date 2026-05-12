<?php

namespace roilafx\Commerced3\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $table = 'commerce_order_statuses';

    protected $fillable = [
        'title',
        'alias',
        'color',
        'notify',
        'default',
        'canbepaid',
    ];

    protected $casts = [
        'notify'    => 'boolean',
        'default'   => 'boolean',
        'canbepaid' => 'boolean',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];

    public const NEW      = 1;
    public const PROCESS  = 2;
    public const PAID     = 3;
    public const SHIPPED  = 4;
    public const CANCELED = 5;
    public const COMPLETE = 6;
    public const PENDING  = 7;

    /**
     * ID статусов, которые участвуют в отчётах по выручке.
     * Новый, В обработке, Оплачен, Доставлен.
     */
    public const REPORTABLE_STATUSES = [ 3, 4, 6 ];

    /**
     * Заказы в этом статусе.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'status_id');
    }
}