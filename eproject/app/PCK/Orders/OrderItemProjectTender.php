<?php namespace PCK\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderItemProjectTender extends Model
{
    protected $table = 'order_item_project_tenders';

    public function orderItem()
    {
        return $this->belongsTo('PCK\Orders\OrderItem');
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }
}