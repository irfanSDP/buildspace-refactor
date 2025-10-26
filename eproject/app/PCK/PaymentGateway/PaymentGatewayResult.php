<?php namespace PCK\PaymentGateway;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayResult extends Model
{
    protected $table = 'payment_gateway_results';

    protected $fillable = [
        'payment_gateway',
        'transaction_id',
        'reference_id',
        'status',
        'info',
        'verified',
        'is_ipn',
        'data'
    ];

    public function order()
    {
        return $this->belongsTo('PCK\Orders\Order', 'reference_id', 'reference_id');
    }

    public function getOrderItem()
    {
        $order = $this->order;

        if ($order) {
            $orderSub = $order->orderSubs->first();

            if ($orderSub) {
                return $orderSub->orderItems->first();
            }
        }

        return null;
    }
}