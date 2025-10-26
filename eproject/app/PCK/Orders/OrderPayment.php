<?php namespace PCK\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = 'order_payments';

    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    const STATUS_CANCELLED = 'CANCELLED';

    public function order()
    {
        return $this->belongsTo('PCK\Orders\Order');
    }

    public function paymentResult()
    {
        return $this->hasOne('PCK\PaymentGateway\PaymentGatewayResult', 'transaction_id', 'transaction_id');
    }

    public function getTypeLabel($type)
    {
        switch ($type) {
            case self::STATUS_PENDING:
                $label = trans('orders.orderPaymentStatusPending');
                break;

            case self::STATUS_SUCCESS:
                $label = trans('orders.orderPaymentStatusSuccess');
                break;

            case self::STATUS_FAILED:
                $label = trans('orders.orderPaymentStatusFailed');
                break;

            case self::STATUS_CANCELLED:
                $label = trans('orders.orderPaymentStatusCancelled');
                break;

            default:
                throw new \Exception('Invalid type');
        }
        return $label;
    }
}