<?php namespace PCK\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderSub extends Model
{
    protected $table = 'order_subs';

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function order()
    {
        return $this->belongsTo('PCK\Orders\Order');
    }

    public function orderItems()
    {
        return $this->hasMany('PCK\Orders\OrderItem');
    }
}