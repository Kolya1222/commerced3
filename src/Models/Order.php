<?php

namespace roilafx\Commerced3\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'commerce_orders';

    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'email',
        'amount',
        'currency',
        'lang',
        'fields',
        'status_id',
        'hash',
    ];

    protected $casts = [
        'amount'      => 'float',
        'status_id'   => 'integer',
        'customer_id' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Товары в заказе.
     */
    public function products()
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

    /**
     * Платежи по заказу.
     */
    public function payments()
    {
        return $this->hasMany(OrderPayment::class, 'order_id');
    }

    /**
     * История изменения статусов.
     */
    public function history()
    {
        return $this->hasMany(OrderHistory::class, 'order_id');
    }

    /**
     * Текущий статус заказа.
     */
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }
    /**
     * Только заказы, участвующие в финансовой аналитике.
     * Не Отменённые, не Завершённые, не Ожидание.
     */
    public function scopeReportable($query)
    {
        return $query->whereIn('status_id', OrderStatus::REPORTABLE_STATUSES);
    }

    /**
     * За период в днях.
     */
    public function scopePeriod($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
