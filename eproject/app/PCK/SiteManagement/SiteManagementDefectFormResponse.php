<?php namespace PCK\SiteManagement;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

class SiteManagementDefectFormResponse extends Model{

	protected $table = 'site_management_defect_form_responses';

	CONST RESPONSE_ACCEPT = 1;
	CONST RESPONSE_REJECT = 2;
	CONST RESPONSE_BACKCHARGE = 3;
	CONST RESPONSE_MCAR = 4;
	CONST RESPONSE_RESPOND= 5;

	use ModuleAttachmentTrait;
	
	public static function getResponseText($responseId)
	{
		$responseText = array(self::RESPONSE_ACCEPT => "ACCEPT", 
							  self::RESPONSE_REJECT => "REJECT",
							  self::RESPONSE_BACKCHARGE => "BACKCHARGE", 
							  self::RESPONSE_MCAR => "MCAR", 
							  self::RESPONSE_RESPOND => "RESPONDED");

		return $responseText[$responseId];
	} 

	public static function checkRecordExists($form_id)
	{
		return static::where("site_management_defect_id", $form_id)->first(); 
	}

	public function user()
	{
		return $this->belongsTo('PCK\Users\User','user_id');
	}

	public function siteManagementDefect()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteManagementDefect','site_management_defect_id');
	}

	public static function checkRejectNumber($form_id){

		return static::where("site_management_defect_id", $form_id)
					 ->where("responseId", static::RESPONSE_REJECT)
					 ->count();
	}
}