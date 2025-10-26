<?php 

namespace PCK\OpenTenderBanners;
use PCK\Subsidiaries\Subsidiary; 
use Illuminate\Database\Eloquent\Model;

class OpenTenderBanners extends Model {
    const ORDER_1 = 1;
    const ORDER_2 = 2;
    const ORDER_3 = 3;
    const ORDER_4 = 4;
    const ORDER_5 = 5;
    const ORDER_6 = 6;

	protected $table = 'open_tender_banners';

	protected $fillable = [ 'image', 'display_order', 'start_time', 'end_time',  'created_by' ];

    public function created_by()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public static function display_order()
    {
       $display_order[self::ORDER_1 ] = '1';
       $display_order[self::ORDER_2 ] = '2';
       $display_order[self::ORDER_3 ] = '3';
       $display_order[self::ORDER_4 ] = '4';
       $display_order[self::ORDER_5 ] = '5';
       $display_order[self::ORDER_6 ] = '6';

       return $display_order;
    }

}