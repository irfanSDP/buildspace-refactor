<?php namespace PCK\SiteManagement\SiteDiary;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\SiteManagement\SiteManagementUserPermission;

class SiteManagementSiteDiaryRejectedMaterial extends Model
{
    protected $fillable = ['rejected_material_id', 'site_diary_id'];

    public function rejectedMaterial()
	{
		return $this->belongsTo('PCK\SiteManagement\RejectedMaterial','rejected_material_id');
	}

    public function general()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse','site_diary_id');
	}
}