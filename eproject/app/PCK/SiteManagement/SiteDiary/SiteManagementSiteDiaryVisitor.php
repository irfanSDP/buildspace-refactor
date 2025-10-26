<?php namespace PCK\SiteManagement\SiteDiary;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\SiteManagement\SiteManagementUserPermission;

class SiteManagementSiteDiaryVisitor extends Model
{
    protected $fillable = ['visitor_name','visitor_company_name','visitor_time_in','visitor_time_out', 'site_diary_id'];


    public function general()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse','site_diary_id');
	}
}