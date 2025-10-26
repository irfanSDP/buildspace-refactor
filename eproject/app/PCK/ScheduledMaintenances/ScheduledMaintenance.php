<?php namespace PCK\ScheduledMaintenances;

use Illuminate\Database\Eloquent\Model;

class ScheduledMaintenance extends Model {
	
	protected $table = 'scheduled_maintenance';

	protected $fillable = [ 'message', 'start_time', 'end_time', 'status', 'image', 'created_by' ];

  public function created_by()
  {
    return $this->belongsTo('PCK\Users\User', 'created_by');
  }

}