<?php namespace PCK\SiteManagement;

use Illuminate\Database\Eloquent\Model;
use PCK\Verifier\Verifiable;

class SiteManagementDefectBackchargeDetail extends Model implements Verifiable{

	use VerifiableTrait;

	CONST STATUS_BACKCHARGE = 0;
	CONST STATUS_BACKCHARGE_PENDING = 1;
	CONST STATUS_BACKCHARGE_APPROVED = 2;
	CONST STATUS_BACKCHARGE_REJECTED = 3;
	CONST STATUS_BACKCHARGE_SUBMITTED = 4;

	protected $table = "site_management_defect_backcharge_details"; 

	public static function getStatusText($statusId)
	{
		$statusText = array(self::STATUS_BACKCHARGE => "Backcharge",
							self::STATUS_BACKCHARGE_PENDING => "Backcharge Pending",
							self::STATUS_BACKCHARGE_REJECTED => "Backcharge Rejected",
							self::STATUS_BACKCHARGE_SUBMITTED => "Backcharge Submitted");

		return $statusText[$statusId];
	} 

	public static function checkRecordExists($form_id)
	{
		return static::where("site_management_defect_id", $form_id)->first() ? true : false; 
	}

	public function user()
	{
		return $this->belongsTo('PCK\Users\User','user_id');
	}
	
	public function siteManagementDefect()
	{
		return $this->belongsTo('PCK\SiteManagement\SiteManagementDefect','site_management_defect_id');
	}

	public function getProject()
    {
        return $this->siteManagementDefect->project;
    }

	public function getObjectDescription()
    {
        return trans('siteManagement.backCharge');
	}
	
	public function getModuleName()
	{
		return trans('siteManagement.site_management') . ' ' . trans('siteManagement.defect');
	}
}