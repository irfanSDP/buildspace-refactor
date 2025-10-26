<?php namespace PCK\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    const TYPE_VENDOR_REG = 'VENDOR_REG';
    const TYPE_VENDOR_RENEWAL = 'VENDOR_RENEWAL';
    const TYPE_OPEN_TENDER = 'OPEN_TENDER';

    public function orderSub()
    {
        return $this->belongsTo('PCK\Orders\OrderSub');
    }

    public function orderItemProjectTender()
    {
        return $this->hasOne('PCK\Orders\OrderItemProjectTender');
    }

    public function orderItemVendorRegPayment()
    {
        return $this->hasOne('PCK\Orders\OrderItemVendorRegPayment');
    }

    public function getProjectAttribute()
    {
        if ($this->orderItemProjectTender) {
            return $this->orderItemProjectTender->project;
        }

        return null;
    }

    public function getTenderAttribute()
    {
        if ($this->orderItemProjectTender) {
            return $this->orderItemProjectTender->tender;
        }

        return null;
    }

    public static function getTypeLabel($type)
    {
        switch($type)
        {
            case self::TYPE_VENDOR_REG:
                $label = trans('orders.orderTypeVendorReg');
                break;

            case self::TYPE_VENDOR_RENEWAL:
                $label = trans('orders.orderTypeVendorRenewal');
                break;

            case self::TYPE_OPEN_TENDER:
                $label = trans('orders.orderTypeOpenTender');
                break;

            default:
                throw new \Exception('Invalid type');
        }

        return $label;
    }
}