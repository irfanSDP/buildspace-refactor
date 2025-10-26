<?php namespace PCK\Menus;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model {

    protected $fillable = [ 'contract_id', 'name', 'icon_class', 'route_name', 'priority' ];

    public function contract()
    {
        return $this->belongsTo('PCK\Contracts\Contract');
    }

}