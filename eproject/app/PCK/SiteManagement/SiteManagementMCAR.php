<?php namespace PCK\SiteManagement;

use Illuminate\Database\Eloquent\Model;

class SiteManagementMCAR extends Model{

	protected $table = 'site_management_mcar';

	CONST MCAR_NONE = 0;
	CONST MCAR_SUBMIT_FORM = 1;
	CONST MCAR_PENDING_REPLY = 2;
	CONST MCAR_PENDING_VERIFY = 3;
	CONST MCAR_VERIFIED = 4;

	public static function getMCARText($mcarId)
	{
		$mcarText = array(self::MCAR_NONE => "None",
						  self::MCAR_SUBMIT_FORM => "Submit Form",
						  self::MCAR_PENDING_REPLY => "Pending Reply",
						  self::MCAR_PENDING_VERIFY => "Pending Verify",
						  self::MCAR_VERIFIED => "Verified");

		return $mcarText[$mcarId];
	} 

	public static function checkRecordExists($form_id)
	{
		return static::where("site_management_defect_id", $form_id)->first();
	}

	public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}

	public function company()
	{
		return $this->belongsTo('PCK\Companies\Company','contractor_id');
	}

	public function user()
    {
        return $this->belongsTo('PCK\Users\User','submitted_user_id');
    }

    public function siteManagementDefect()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteManagementDefect','site_management_defect_id');
	}

	public function MCARFormResponse()
	{
		return $this->hasOne('PCK\SiteManagement\SiteManagementMCARFormResponse', 'site_management_mcar_id');
	}

}