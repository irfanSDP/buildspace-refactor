<?php namespace PCK\InstructionsToContractors;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\SiteManagement\SiteManagementUserPermission;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\Helpers;

class InstructionsToContractor extends Model implements Verifiable {

    use ModuleAttachmentTrait;

    const STATUS_REJECT = 3;
    const STATUS_PENDING_FOR_APPROVAL = 2;
    const STATUS_OPEN = 1;
    const STATUS_APPROVED = 4;
	
	protected $table = 'instructions_to_contractors';

    public $fillable = ["instruction_date", "instruction", "submitted_by", "status", "project_id"];

    public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}
    
    public function submittedUser()
    {
        return $this->belongsTo('PCK\Users\User','submitted_by');
    }

    public static function processQuery($user, $project){

		$query = InstructionsToContractor::where("project_id", $project->id);  
		return $query; 
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
	
    public function getVerifiers($project)
    {
        $verifiers = [];

        return SiteManagementUserPermission::getAssignedVerifiers($project, SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR);
    }

	public function getOnApprovedView()
    {
        return 'instructionToContractor.approved';
    }

    public function getOnRejectedView()
    {
        return 'instructionToContractor.rejected';
    }

    public function getOnPendingView()
    {
        return 'instructionToContractor.pending';
    }

    /**
     * Route to relevant page.
     *
     * @return string
     */
    public function getModuleName()
    {
        return trans('modules.instructionToContractor');
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getObjectDescription()
    {
        return $this->instruction;
    }

    /**
     * Route to relevant page.
     *
     * @return string
     */
    public function getRoute(){
        return route('instruction-to-contractor.show', [$this->project->id, $this->id]);
    }

    public function getOnApprovedNotifyList()
    {
        $users = SiteManagementUserPermission::getUserList(SiteManagementUserPermission::MODULE_IDENTIFIER_INSTRUCTION_TO_CONTRACTOR);

        return $users;
    }

    public function getOnRejectedNotifyList()
    {
        $users = array();

        $user = User::find($this->submitted_by);

        if( $user->stillInSameAssignedCompany($this->project, $this->created_at) )
        {
            $users[] = $user;
        }

        return $users;
    }

    public function getOnApprovedFunction(){}

    public function getOnRejectedFunction(){}

    public function isLocked()
    {
        return in_array($this->status, [self:: STATUS_PENDING_FOR_APPROVAL, self::STATUS_APPROVED]);
    }

    public function isApproved()
    {
        return $this->status == self::STATUS_APPROVED;
    }

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
     * get the customized email subject.
     *
     * @return string
     */
    public function getEmailSubject($locale)
    {
        return trans('instructiontocontractor.instructionToContractorNotification', [], 'messages', $locale);
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
     * get the id of the user who submitted the form for approval.
     *
     * @return int|null
     */
    public function getSubmitterId(){}

    public static function getPendingSiteManagementInstructionToContractor(User $user, $includeFutureTasks, $project = null)
    {
        $pendingInstructionToContractors = [];

		if($project)
		{
			foreach($project->instructionsToContractors as $instructionToContractor)
			{
                $isCurrentVerifier = Verifier::isCurrentVerifier($user, $instructionToContractor);
                $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $instructionToContractor) : $isCurrentVerifier;
                $daysPending = Helpers::getDaysPending($instructionToContractor);

                if($proceed)
                {
                    $instructionToContractor['daysPending']    = $daysPending;
                    $instructionToContractor['is_future_task'] = ! $isCurrentVerifier;

                    $pendingInstructionToContractors[$instructionToContractor->id] = $instructionToContractor;
                }
			}
		}
		else
		{
            $records = Verifier::where('verifier_id', $user->id)
                ->where('object_type', InstructionsToContractor::class)
                ->get();

            foreach($records as $record)
            {
                $instructionToContractor = InstructionsToContractor::find($record->object_id);

                if($instructionToContractor)
                {
                    $isCurrentVerifier  = Verifier::isCurrentVerifier($user, $instructionToContractor);
                    $proceed            = $includeFutureTasks ? Verifier::isAVerifierInline($user, $instructionToContractor) : $isCurrentVerifier;
                    $daysPending        = Helpers::getDaysPending($instructionToContractor);
    
                    if($instructionToContractor->project && $proceed)
                    {
                        $instructionToContractor['daysPending']    = $daysPending;
                        $instructionToContractor['is_future_task'] = ! $isCurrentVerifier;
                        $instructionToContractor['company_id']     = $instructionToContractor->project->business_unit_id;
    
                        $pendingInstructionToContractors[$instructionToContractor->id] = $instructionToContractor;
                    }
                }
            }
		}

		return $pendingInstructionToContractors;
    }

}