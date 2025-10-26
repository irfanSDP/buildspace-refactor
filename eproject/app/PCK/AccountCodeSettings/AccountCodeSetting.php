<?php namespace PCK\AccountCodeSettings;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\Users\User;
use PCK\ModulePermission\ModulePermission;
use PCK\Tenders\Tender;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class AccountCodeSetting extends Model implements Verifiable
{
    protected $table = 'account_code_settings';

    const STATUS_OPEN = 1;
    const STATUS_PENDING_FOR_APPROVAL = 2;
    const STATUS_APPROVED = 4;

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function apportionmentType()
    {
        return $this->belongsTo('PCK\AccountCodeSettings\ApportionmentType');
    }

    public function vendorCategory()
    {
        return $this->belongsTo('PCK\VendorCategory\VendorCategory');
    }

    public static function getStatusText($status)
    {
        $statusText = '';

        switch($status)
        {
            case self::STATUS_OPEN:
                $statusText = trans('accountCodes.open');
                break;
            case self::STATUS_PENDING_FOR_APPROVAL:
                $statusText = trans('accountCodes.pendingForApproval');
                break;
            case self::STATUS_APPROVED:
                $statusText = trans('accountCodes.approved');
                break;
            default:
                // error
        }

        return $statusText;
    }

    public static function getStatusKeyValues()
    {
        return [
            self::STATUS_OPEN                 => self::getStatusText(self::STATUS_OPEN),
            self::STATUS_PENDING_FOR_APPROVAL => self::getStatusText(self::STATUS_PENDING_FOR_APPROVAL),
            self::STATUS_APPROVED             => self::getStatusText(self::STATUS_APPROVED),
        ];
    }

    public function isLocked()
    {
        return in_array($this->status, [self:: STATUS_PENDING_FOR_APPROVAL, self::STATUS_APPROVED]);
    }

    public function isApproved()
    {
        return $this->status == self::STATUS_APPROVED;
    }

    public function canApproveOrRejectApproval(User $user)
    {
        if($this->status != self::STATUS_PENDING_FOR_APPROVAL) return false;

        return Verifier::isCurrentVerifier($user, $this);
    }

    public function getObjectDescription()
    {
        return null;
    }

    public function getOnApprovedView()
    {
        return 'accountCodeSetting.approved';
    }

    public function getOnRejectedView()
    {
        return 'accountCodeSetting.rejected';
    }

    public function getOnPendingView()
    {
        return 'accountCodeSetting.pending';
    }

    public function getRoute()
    {
        return route('finance.account.code.settings.show', [$this->project->id]);
    }

    public function getViewData($locale)
    {
        $viewData = [
            'senderName'			=> \Confide::user()->name,
			'project_title' 		=> $this->project->title,
            'recipientLocale'       => $locale,
        ];
        
        if( ! Verifier::isApproved($this) )
        {
            $viewData['toRoute'] = $this->getRoute();
        }

        return $viewData;
    }

    public function getSubmitterId()
    {
        return $this->updated_by;
    }

    public function getModuleName()
    {
        return trans('modules.accountCodeSettings');
    }

    /**
     * get verifers for finance users with permission for a given subsidiary
     */
    public function getVerifiers()
    {
        $user = \Confide::user();
        $verifiers = [];
		$financeUsers = ModulePermission::getUserList(ModulePermission::MODULE_ID_FINANCE);
        $visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

		foreach($financeUsers as $financeUser)
		{
			if(in_array($this->project->subsidiary->id, $visibleSubsidiaryIds))
			{
				array_push($verifiers, $financeUser);
			}
        }

        return $verifiers;
    }

    public function getOnApprovedNotifyList()
    {
        $usersToNotify  = [];
        $roles          = array_unique(Tender::rolesAllowedToUseModule($this->project));
        $contractGroups = ContractGroup::whereIn('group', $roles)->get();

        $contractGroupProjectUsers = ContractGroupProjectUser::where('project_id', '=', $this->project->id)
            ->whereIn('contract_group_id', $contractGroups->lists('id'))
            ->where('is_contract_group_project_owner', '=', true)
            ->get();

        foreach($contractGroupProjectUsers as $contractGroupProjectUser)
        {
            $recipient = User::find($contractGroupProjectUser->user_id);

            if( ( ! $this->project->contractor_access_enabled ) && $recipient->hasCompanyProjectRole($this->project, Role::CONTRACTOR) ) continue;

            array_push($usersToNotify, $recipient);
        }

        // get all users that are involved in a given round of verification
        // users involved are automatically finance users since only finance users will be the verifiers
        foreach(Verifier::getAssignedVerifierRecords($this)as $verifierRecord)
        {
            array_push($usersToNotify, $verifierRecord->verifier);
        }

        return $usersToNotify;
    }

    public function getOnRejectedNotifyList()
    {
        $users = array();

        $user = User::find($this->submitted_for_approval_by);

        if( $user->stillInSameAssignedCompany($this->project, $this->created_at) )
        {
            $users[] = $user;
        }

        return $users;
    }

    public function getOnApprovedFunction()
    {
    }

    public function getOnRejectedFunction()
    {
    }

    public function onReview()
    {
        if(Verifier::isApproved($this))
        {
            $this->status = self::STATUS_APPROVED;
            $this->save();

            \Queue::push('PCK\QueueJobs\ExternalOutboundAPI', [
                'module'                  => 'AwardedContractor',
                'account_code_setting_id' => $this->id,
            ], 'ext_app_outbound');
        }

        if(Verifier::isRejected($this))
        {
            $this->status = self::STATUS_OPEN;
            $this->save();
        }
    }

    public function getEmailSubject($locale)
    {
        return trans('accountCodes.accountCodeSettingNotification', [], 'messages', $locale);
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getDaysPendingAttribute()
    {
        $then = Carbon::parse($this->updated_at);
        $now = Carbon::now();

        return $then->diffInDays($now);
    }

    public static function getInUseApportionmentTypeById($apportionmentTypeId)
    {
        return self::where('apportionment_type_id', $apportionmentTypeId)->get();
    }
}

