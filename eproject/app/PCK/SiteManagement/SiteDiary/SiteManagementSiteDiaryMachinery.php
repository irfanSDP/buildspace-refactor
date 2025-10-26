<?php namespace PCK\SiteManagement\SiteDiary;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\SiteManagement\SiteManagementUserPermission;

class SiteManagementSiteDiaryMachinery extends Model
{
	protected $table = "site_management_site_diary_machinery";
    protected $fillable = ['machinery_id','value','site_diary_id'];


    public function general()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse','site_diary_id');
	}

    public function labour()
	{
		return $this->belongsTo('PCK\SiteManagement\Machinery','machinery_id');
	}
}