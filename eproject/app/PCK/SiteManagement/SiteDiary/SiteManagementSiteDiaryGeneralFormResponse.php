<?php namespace PCK\SiteManagement\SiteDiary;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\SiteManagement\SiteManagementUserPermission;

class SiteManagementSiteDiaryGeneralFormResponse extends Model implements Verifiable
{
    const STATUS_OPEN = 1;
    const STATUS_PENDING_FOR_APPROVAL = 2;
    const STATUS_REJECT = 3;
    const STATUS_APPROVED = 4;

    protected $fillable = [ 'general_date', 'general_time_in','general_time_out','general_day','general_physical_progress','general_plan_progress','weather_time_from', 'weather_time_to','weather_id',
                            'labour_project_manager','labour_site_agent','labour_supervisor','machinery_excavator','machinery_backhoe','machinery_crane','rejected_material_id',
                            'visitor_name','visitor_company_name','visitor_time_in','visitor_time_out','submitted_by','project_id', 'status'];


    public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}
    
    public function submittedUser()
    {
        return $this->belongsTo('PCK\Users\User','submitted_by');
    }

    public function weather()
	{
		return $this->belongsTo('PCK\Weathers\Weather','weather_id');
	}

    public function submittedForApprovalBy()
    {
        return $this->belongsTo('PCK\Users\User','submitted_for_approval_by');
    }

    public static function getStatusText($status)
    {
        $statusText = '';

        switch($status)
        {
            case self::STATUS_REJECT:
                $statusText = "Rejected";
                break;
            case self::STATUS_PENDING_FOR_APPROVAL:
                $statusText = "Pending For Approval";
                break;
            case self::STATUS_OPEN:
                $statusText = "Open";
                break;
            case self::STATUS_APPROVED:
                $statusText = "Approved";
                break;
            default: break;
        }

        return $statusText;
    }

    public function getDays()
    {
        return [
			"Sunday",
			"Monday",
			"Tuesday",
			"Wednesday",
			"Thursday",
			"Friday",
			"Saturday"
		];
    }

    public function getOnApprovedView()
    {
        return 'siteManagementSiteDiary.approved';
    }

    public function getOnRejectedView()
    {
        return 'siteManagementSiteDiary.rejected';
    }

    public function getOnPendingView()
    {
        return 'siteManagementSiteDiary.pending';
    }

    public function getModuleName()
    {
        return trans('modules.siteDiary');
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getObjectDescription()
    {
        return trans('siteManagementSiteDiary.site_diary_task');
    }

    public function isLocked()
    {
        return in_array($this->status, [self:: STATUS_PENDING_FOR_APPROVAL, self::STATUS_APPROVED]);
    }

    public function isApproved()
    {
        return $this->status == self::STATUS_APPROVED;
    }

    /**
     * Route to relevant page.
     *
     * @return string
     */
    public function getRoute(){
        return route('site-management-site-diary.general-form.show', [$this->project->id, $this->id]);
    }

    public function getEmailSubject($locale)
    {
        return trans('siteManagementSiteDiary.siteDiaryNotification', [], 'messages', $locale);
    }

    /**
     * Data for the email view.
     *
     * @return array
     */
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

    /**
     * User objects.
     *
     * @return array
     */
    public function getOnApprovedNotifyList()
    {
        $siteDiaryUsers = SiteManagementUserPermission::getUserList(SiteManagementUserPermission::MODULE_IDENTIFIER_SITE_DIARY, $this->project->id);

        return $siteDiaryUsers;
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


    /**
     * A closure for when the all verifiers have approved.
     *
     * @return \Closure
     */
    public function getOnApprovedFunction(){}

    /**
     * A closure for when the all verifiers have approved.
     *
     * @return \Closure
     */
    public function getOnRejectedFunction(){}

    /**
     * A closure for when the object has been reviewed.
     *
     * @return \Closure
     */
    public function onReview()
    {
        if(Verifier::isApproved($this))
        {
            $this->status = self::STATUS_APPROVED;
            $this->save();

            \Queue::push('PCK\QueueJobs\ExternalOutboundAPI', [
                'module'                  => 'SiteManagement',
                'account_code_setting_id' => $this->id,
            ], 'ext_app_outbound');
        }

        if(Verifier::isRejected($this))
        {
            $this->status = self::STATUS_OPEN;
            $this->save();
        }
    }


    /**
     * get the id of the user who submitted the form for approval.
     *
     * @return int|null
     */
    public function getSubmitterId(){}

}