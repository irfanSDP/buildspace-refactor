<?php namespace PCK\SiteManagement\SiteDiary;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\SiteManagement\SiteManagementUserPermission;

class SiteManagementSiteDiaryLabour extends Model
{
    protected $fillable = ['labour_id','value','site_diary_id'];


    public function general()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse','site_diary_id');
	}

    public function labour()
	{
		return $this->belongsTo('PCK\SiteManagement\Labour','labour_id');
	}
}