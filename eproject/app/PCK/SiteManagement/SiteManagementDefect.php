<?php namespace PCK\SiteManagement;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Base\ModuleAttachmentTrait;
use PCK\SiteManagement\SiteManagementUserPermission;

class SiteManagementDefect extends Model
{
	CONST STATUS_OPEN = 1;
	CONST STATUS_CLOSED = 2;
	CONST STATUS_FIRST_REJECT = 3;
	CONST STATUS_SECOND_REJECT = 4;
	CONST STATUS_RESPONDED = 5;
	CONST STATUS_BACKCHARGE = 6;
	CONST STATUS_BACKCHARGE_PENDING = 7;
	CONST STATUS_REJECT = 8;
	CONST STATUS_BACKCHARGE_APPROVED = 9;
	CONST STATUS_BACKCHARGE_REJECTED = 10;
	CONST STATUS_BACKCHARGE_SUBMITTED = 11;

	use ModuleAttachmentTrait;

	public static function getStatusText($statusId)
	{
		$statusText = array(self::STATUS_OPEN => "Open",
							self::STATUS_CLOSED => "Closed",
							self::STATUS_FIRST_REJECT => "First Reject",
							self::STATUS_SECOND_REJECT => "Second Reject",
							self::STATUS_RESPONDED => "Responded",
							self::STATUS_BACKCHARGE => "Backcharge",
							self::STATUS_BACKCHARGE_PENDING => "Backcharge Pending",
							self::STATUS_REJECT => "Rejected",
							self::STATUS_BACKCHARGE_APPROVED => "Backcharge Approved",
							self::STATUS_BACKCHARGE_REJECTED => "Backcharge Rejected",
							self::STATUS_BACKCHARGE_SUBMITTED => "Backcharge Submitted");

		return $statusText[$statusId];
	}

	public static function checkStatus($form_id)
	{
		return static::find($form_id)->status_id;
	}

	public static function isDefectAssignedContractor(User $user, $form_id)
    {
       $defectContractor_id = static::find($form_id)->contractor_id;

       return ($user->company->id == $defectContractor_id) ? true : false;
    }

	public static function checkAssignedPicCanRespond(User $user, $form_id)
	{
		$record = static::where("id", $form_id)->where("pic_user_id", $user->id)->first();

		return (! empty($record));
	}

	public static function checkSubmittedUserCanRespond(User $user, $form_id)
	{
		$record = static::where("id", $form_id)->where("submitted_by", $user->id)->first();

		return (! empty($record));
	}

	public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}

	public function company()
	{
		return $this->belongsTo('PCK\Companies\Company','contractor_id');
	}

	public function billColumnSetting()
	{
		return $this->belongsTo('PCK\Buildspace\BillColumnSetting','bill_column_setting_id');
	}

	public function projectStructureLocationCode()
	{
		return $this->belongsTo('PCK\Buildspace\ProjectStructureLocationCode','project_structure_location_code_id');
	}

	public function preDefinedLocationCode()
	{
		return $this->belongsTo('PCK\Buildspace\PreDefinedLocationCode','pre_defined_location_code_id');
	}

	public function defectCategory()
	{
		return $this->belongsTo('PCK\Defects\DefectCategory','defect_category_id');
	}

	public function defect()
	{
		return $this->belongsTo('PCK\Defects\Defect','defect_id');
	}

	public function user()
    {
        return $this->belongsTo('PCK\Users\User','pic_user_id');
    }

    public function submittedUser()
    {
        return $this->belongsTo('PCK\Users\User','submitted_by');
    }

    public function siteManagementDefectFormResponses()
    {
    	return $this->hasMany('PCK\SiteManagement\SiteManagementDefectFormResponse', 'site_management_defect_id');
    }

    public function siteManagementDefectBackchargeDetails()
    {
        return $this->hasMany('PCK\SiteManagement\SiteManagementDefectBackchargeDetail', 'site_management_defect_id')->orderBy('created_at', 'asc');
    }

    public function siteManagementMCAR()
    {
    	return $this->hasOne('PCK\SiteManagement\SiteManagementMCAR', 'site_management_defect_id');
    }

    public static function getRecordsForMobileDataByUserPermission(User $user, Array $syncedIds, $userType)
    {
        $siteManagementUserPermTableName = with(new SiteManagementUserPermission)->getTable();

        $query =  SiteManagementDefect::join('projects AS p', 'p.id', '=', 'site_management_defects.project_id')
        ->join($siteManagementUserPermTableName.' AS perm', 'perm.project_id', '=', 'p.id')
        ->select("site_management_defects.id AS id", "mobile_sync_uuid", "site_management_defects.project_id", "project_structure_location_code_id", "pre_defined_location_code_id",
            "contractor_id", "defect_category_id", "defect_id", "remark AS remarks", "pic_user_id", "site_management_defects.created_at", "site_management_defects.submitted_by",
            "site_management_defects.status_id", "site_management_defects.count_reject", "p.parent_project_id")
        ->where('perm.module_identifier', SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT)
        ->where('perm.user_id', $user->id)
        ->whereRaw('p.deleted_at IS NULL');

        switch($userType)
        {
            case SiteManagementUserPermission::USER_TYPE_SITE:
                $query->whereRaw('perm.site IS TRUE');
                $query = $query->where(function($query) use ($user){
                    $query->where("site_management_defects.pic_user_id", $user->id)
                    ->orWhere('site_management_defects.submitted_by', $user->id);
                });
                break;
            case SiteManagementUserPermission::USER_TYPE_QA_QC_CLIENT:
                $query->whereRaw('perm.qa_qc_client IS TRUE');
                $query = $query->where(function($query) use ($user){
                    $query->where("site_management_defects.pic_user_id", $user->id)
                    ->orWhere('site_management_defects.submitted_by', $user->id);
                });
                break;
            case SiteManagementUserPermission::USER_TYPE_PM:
                $query->whereRaw('perm.pm IS TRUE');
                break;
            case SiteManagementUserPermission::USER_TYPE_QS:
                $query->whereRaw('perm.qs IS TRUE');
                $query = $query->where(function($query){
                    $query->where("site_management_defects.status_id", SiteManagementDefect::STATUS_BACKCHARGE)
                    ->orWhere('site_management_defects.status_id', SiteManagementDefect::STATUS_BACKCHARGE_PENDING)
                    ->orWhere('site_management_defects.status_id', SiteManagementDefect::STATUS_BACKCHARGE_SUBMITTED)
                    ->orWhere('site_management_defects.status_id', SiteManagementDefect::STATUS_BACKCHARGE_REJECTED);
                });
                break;
            default:
                throw new \Exception('Invalid user type');
        }

        if(!empty($syncedIds))
        {
            $query->whereNotIn('site_management_defects.id', $syncedIds);
        }

        return $query->orderBy("site_management_defects.project_id", "DESC")
            ->orderBy("site_management_defects.created_at", 'DESC')
            ->get()
            ->keyBy('id')
            ->toArray();
    }
}
