<?php namespace PCK\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderItemVendorRegPayment extends Model
{
    protected $table = 'order_item_vendor_reg_payments';

    public function orderItem()
    {
        return $this->belongsTo('PCK\Orders\OrderItem');
    }

    public function vendorRegistrationPayment()
    {
        return $this->belongsTo('PCK\VendorRegistration\Payment\VendorRegistrationPayment');
    }
}