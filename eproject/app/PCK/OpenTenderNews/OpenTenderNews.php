<?php 

namespace PCK\OpenTenderNews;
use PCK\Subsidiaries\Subsidiary; 
use Illuminate\Database\Eloquent\Model;

class OpenTenderNews extends Model {
    const NEWS_STATUS_ACTIVE = 1;
    const NEWS_STATUS_DEACTIVE = 2;

	protected $table = 'open_tender_news';

	protected $fillable = [ 'description', 'start_time', 'end_time', 'status', 'subsidiary_id',  'created_by' ];

    public function subsidiary()
    {
        return $this->belongsTo('PCK\Subsidiaries\Subsidiary');
    }

    public function created_by()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public static function subsidiariesList()
    {
        return Subsidiary::lists('name', 'id');
    }

    public static function status()
    {
       $status[self::NEWS_STATUS_ACTIVE] = 'Active';
       $status[self::NEWS_STATUS_DEACTIVE] = 'Deactive';

       return $status;
    }

}