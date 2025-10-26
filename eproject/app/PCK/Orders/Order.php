<?php namespace PCK\Orders;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    const ORIGIN_REG = 'REG';
    const ORIGIN_RENEWAL = 'RENEWAL';
    const ORIGIN_TENDER = 'TENDER';

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function orderSubs()
    {
        return $this->hasMany('PCK\Orders\OrderSub');
    }

    public function orderPayment()
    {
        return $this->hasOne('PCK\Orders\OrderPayment');
    }

    public function paymentResults()
    {
        return $this->hasMany('PCK\PaymentGateway\PaymentGatewayResult', 'reference_id', 'reference_id');
    }
}