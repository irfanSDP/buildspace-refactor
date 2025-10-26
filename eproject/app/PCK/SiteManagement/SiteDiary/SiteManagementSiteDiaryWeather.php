<?php namespace PCK\SiteManagement\SiteDiary;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\SiteManagement\SiteManagementUserPermission;

class SiteManagementSiteDiaryWeather extends Model
{
    protected $fillable = ['weather_time_from', 'weather_time_to','weather_id', 'site_diary_id'];


    public function weather()
	{
		return $this->belongsTo('PCK\Weathers\Weather','weather_id');
	}

    public function general()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse','site_diary_id');
	}


}