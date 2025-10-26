<?php namespace PCK\SiteManagement;

use Illuminate\Database\Eloquent\Model;

class SiteManagementMCARFormResponse extends Model{

	CONST VERIFIED_NONE = 1;
	CONST VERIFIED_SATISFACTORY = 2;
	CONST VERIFIED_NOT_SATISFACTORY = 3;
	CONST APPLICABLE_NONE = 1;
	CONST APPLICABLE_YES = 2;
	CONST APPLICABLE_NO= 3;

	protected $table = 'site_management_mcar_form_responses';

	public static function getStatusText($statusId)
	{
		$statusText = array(self::VERIFIED_NONE => "none",
							self::VERIFIED_SATISFACTORY => "Satisfied",
							self::VERIFIED_NOT_SATISFACTORY => "Not Satisfied",
							self::APPLICABLE_NONE => "none",
							self::APPLICABLE_YES => "Yes",
							self::APPLICABLE_NO => "No");

		return $statusText[$statusId];
	} 

	public static function checkRecordExists($form_id)
	{
		return static::where("site_management_defect_id", $form_id)->first();
	}

	public function user()
    {
        return $this->belongsTo('PCK\Users\User','submitted_user_id');
    }

    public function verifier()
    {
        return $this->belongsTo('PCK\Users\User','verifier_id');
    }


    public function siteManagementMCAR()
    {
    	return $this->belongsTo('PCK\SiteManagement\SiteManagementMCAR', 'site_management_mcar_id');
    }


}