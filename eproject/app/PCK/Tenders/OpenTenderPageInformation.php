<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\Helpers\ModelOperations;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\Users\User;
use PCK\Base\Helpers;

class OpenTenderPageInformation extends Model implements Verifiable {

    const TYPE_QUOTATION = 1;
    const TYPE_TENDER = 2;

    const OPEN_TENDER_STATUS_ACTIVE = 1;
    const OPEN_TENDER_STATUS_CANCELLED = 2;

    const STATUS_OPEN = 1;
    const STATUS_PENDING_FOR_APPROVAL = 2;
    const STATUS_REJECT = 3;
    const STATUS_APPROVED = 4;

    protected $table = "open_tender_page_information";

    protected $fillable = ['tender_id', 'project_id','created_by','open_tender_type','open_tender_number','open_tender_date_from', 'open_tender_date_to', 'calling_date', 'closing_date', 'deliver_address', 'briefing_time', 'briefing_address', 'special_permission', 'local_company_only', 'briefing_address', 'open_tender_price', 'open_tender_status'];

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function submittedForApprovalBy()
    {
        return $this->belongsTo('PCK\Users\User','submitted_for_approval_by');
    }

    public static function processInput($input)
	{
		foreach($input as $key => $value)
		{
			if($input[$key] == "")
			{
				$input[$key] = NULL;
			}

			if($key == "_method" || $key == "_token" || $key == "Submit" || $key == "Save" || $key == "form_type" || $key == "files")
			{
				unset($input[$key]);
			}
		}

		return $input;
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

    public static function openTenderType()
    {
       $tenderType[self::TYPE_QUOTATION] = trans('openTender.quotation');
       $tenderType[self::TYPE_TENDER] = trans('openTender.tender');

       return $tenderType;
    }

    public static function openTenderStatus()
    {
       $tenderStatus[self::OPEN_TENDER_STATUS_ACTIVE] = trans('openTender.active');
       $tenderStatus[self::OPEN_TENDER_STATUS_CANCELLED] = trans('openTender.cancelled');

       return $tenderStatus;
    }

    public static function getPendingOpenTenderInfoPage(User $user, $includeFutureTasks, $project = null)
    {
        $pendingOpenTenderPageInformation = [];

		if($project && isset($project->openTenderPageInformation))
		{
			foreach($project->openTenderPageInformation as $openTenderPageInformation)
			{
                $isCurrentVerifier = Verifier::isCurrentVerifier($user, $openTenderPageInformation);
                $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $openTenderPageInformation) : $isCurrentVerifier;

                $openTenderPageInformation['is_future_task'] = ! $isCurrentVerifier;

                if($proceed)
                {
                    $daysPending = Helpers::getDaysPending($openTenderPageInformation);

                    $pendingOpenTenderPageInformation[$openTenderPageInformation->id] = [
                        'project_reference'        => $openTenderPageInformation->getProject()->reference,
                        'parent_project_reference' => ($openTenderPageInformation->getProject()->parentProject) ? $openTenderPageInformation->getProject()->parentProject->reference : null,
                        'project_id'               => $openTenderPageInformation->getProject()->id,
                        'parent_project_id'        => ($openTenderPageInformation->getProject()->parentProject) ? $openTenderPageInformation->getProject()->parentProject->id : null,
                        'project_title'            => $openTenderPageInformation->getProject()->title,
                        'parent_project_title'     => ($openTenderPageInformation->getProject()->parentProject) ? $openTenderPageInformation->getProject()->parentProject->title : null,
                        'project_route'            => route('projects.show', array($openTenderPageInformation->getProject()->id)),
                        'parent_project_route'     => ($openTenderPageInformation->getProject()->parentProject) ? route('projects.show', array($openTenderPageInformation->getProject()->parentProject->id)) : "",
                        'description'              => $openTenderPageInformation->getObjectDescription(),
                        'module'                   => $openTenderPageInformation->getModuleName(),
                        'days_pending'             => $daysPending,
                        'route'                    => $openTenderPageInformation->getRoute()
                    ];
                }
			}
		}
		else
		{
            $records = Verifier::where('verifier_id', $user->id)
                                ->where('object_type', OpenTenderPageInformation::class)
                                ->get();

            foreach($records as $record)
            {
                $openTenderPageInformation = OpenTenderPageInformation::find($record->object_id);

                if($openTenderPageInformation)
                {
                    $isCurrentVerifier  = Verifier::isCurrentVerifier($user, $openTenderPageInformation);
                    $proceed            = $includeFutureTasks ? Verifier::isAVerifierInline($user, $openTenderPageInformation) : $isCurrentVerifier;
    
                    if($openTenderPageInformation->project && $proceed)
                    {
                        $openTenderPageInformation['is_future_task'] = ! $isCurrentVerifier;
                        $openTenderPageInformation['company_id']     = $openTenderPageInformation->project->business_unit_id;

                        $daysPending = Helpers::getDaysPending($openTenderPageInformation);

                        $pendingOpenTenderPageInformation[$openTenderPageInformation->id] = [
                            'project_reference'        => $openTenderPageInformation->getProject()->reference,
                            'parent_project_reference' => ($openTenderPageInformation->getProject()->parentProject) ? $openTenderPageInformation->getProject()->parentProject->reference : null,
                            'project_id'               => $openTenderPageInformation->getProject()->id,
                            'parent_project_id'        => ($openTenderPageInformation->getProject()->parentProject) ? $openTenderPageInformation->getProject()->parentProject->id : null,
                            'project_title'            => $openTenderPageInformation->getProject()->title,
                            'parent_project_title'     => ($openTenderPageInformation->getProject()->parentProject) ? $openTenderPageInformation->getProject()->parentProject->title : null,
                            'project_route'            => route('projects.show', array($openTenderPageInformation->getProject()->id)),
                            'parent_project_route'     => ($openTenderPageInformation->getProject()->parentProject) ? route('projects.show', array($openTenderPageInformation->getProject()->parentProject->id)) : "",
                            'description'              => $openTenderPageInformation->getObjectDescription(),
                            'module'                   => $openTenderPageInformation->getModuleName(),
                            'days_pending'             => $daysPending,
                            'route'                    => $openTenderPageInformation->getRoute()
                        ];                
                    }
                }
            }
		}

		return $pendingOpenTenderPageInformation;
    }

    public function getOnApprovedView()
    {
        return 'openTenderPageInformation.approved';
    }

    public function getOnRejectedView()
    {
        return 'openTenderPageInformation.rejected';
    }

    public function getOnPendingView()
    {
        return 'openTenderPageInformation.pending';
    }

    public function getModuleName()
    {
        return trans('modules.openTenderFormInformation');
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getObjectDescription()
    {
        return trans('openTender.task');
    }

    public function isLocked()
    {
        return in_array($this->status, [self:: STATUS_PENDING_FOR_APPROVAL, self::STATUS_APPROVED]);
    }

    public function isApproved()
    {
        return $this->status == self::STATUS_APPROVED;
    }

    public function isActive()
    {
        return $this->open_tender_status == self::OPEN_TENDER_STATUS_ACTIVE;
    }

    public function priceIsValid()
    {
        if (! is_numeric($this->open_tender_price)) {
            return false;
        }

        $minPrice = 2.00;
        $maxPrice = 99999999.00;

        $price = (float) $this->open_tender_price;

        if ($price < $minPrice || $price > $maxPrice) {
            return false;
        }
        return true;
    }

    /**
     * Route to relevant page.
     *
     * @return string
     */
    public function getRoute(){
        return route('projects.tender.open_tender.get', [$this->project->id, $this->tender->id, 'tenderInfo']);
    }

    public function getEmailSubject($locale)
    {
        return trans('openTender.notification', [], 'messages', $locale);
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
        $notifiers = [\Confide::user()];

        return $notifiers;
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
                'module'                  => 'Tender',
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