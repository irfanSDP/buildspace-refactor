<?php namespace PCK\Buildspace;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Helpers\Mailer;
use PCK\ModulePermission\ModulePermission;
use PCK\Notifications\SystemNotifier;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\BaseVerifier;

class ContractManagementClaimVerifier extends BaseVerifier {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_contract_management_claim_verifiers';

    public $timestamps = false;

    public function bsUser()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'user_id');
    }

    public function bsSubstitute()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'substitute_id');
    }

    public function bsProject()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function getVerifierAttribute()
    {
        return $this->bsUser->Profile->getEProjectUser();
    }

    public function getSubstituteAttribute()
    {
        if( ! $this->bsSubstitute ) return null;

        return $this->bsSubstitute->Profile->getEProjectUser();
    }

    public function getDaysPendingAttribute()
    {
        if( empty( $this->start_at ) ) return false;

        $countDownEndTime = is_null($this->approved) ? Carbon::now() : Carbon::parse($this->verified_at);

        return $countDownEndTime->diffInDays(Carbon::parse($this->start_at));
    }

    public function getProject()
    {
        if( $this->bsProject ) return $this->bsProject->mainInformation->getEProjectProject();

        return null;
    }

    public function getObjectAttribute()
    {
        $class = self::getModuleClass($this->module_identifier);

        return $class::find($this->object_id);
    }

    public function getObjectDescription()
    {
        return $this->object->description;
    }

    public function getModuleName()
    {
        return PostContractClaim::getModuleName($this->module_identifier);
    }

    public function getRoute()
    {
        $user    = \Confide::user();
        $project = $this->bsProject->mainInformation->getEProjectProject();

        $isVerifierWithoutProjectAccess = is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier();

        switch($this->module_identifier)
        {
            case PostContractClaim::TYPE_WATER_DEPOSIT:
                $route = route('contractManagement.waterDeposit.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_DEPOSIT:
                $route = route('contractManagement.deposit.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM:
                $route = route('contractManagement.outOfContractItems.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_PURCHASE_ON_BEHALF:
                $route = route('contractManagement.purchaseOnBehalf.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_ADVANCED_PAYMENT:
                $route = route('contractManagement.advancedPayment.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_WORK_ON_BEHALF:
                $route = route('contractManagement.workOnBehalf.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE:
                $route = route('contractManagement.workOnBehalfBackCharge.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_PENALTY:
                $route = route('contractManagement.penalty.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_PERMIT:
                $route = route('contractManagement.permit.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                $route = route('contractManagement.materialOnSite.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_VARIATION_ORDER:
                $route = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.contractManagement.variationOrder.index', array( $project->id )) : route('contractManagement.variationOrder.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_CLAIM_CERTIFICATE:
                $route = $isVerifierWithoutProjectAccess ? route('topManagementVerifiers.contractManagement.claimCertificate.index', array( $project->id )) : route('contractManagement.claimCertificate.index', array( $project->id ));
                break;
            default:
                throw new \Exception('Invalid module');
        }

        return "{$route}#{$this->object->id}";
    }

    public function getRouteByRecipient(User $user)
    {
        $project = $this->bsProject->mainInformation->getEProjectProject();
        $route   = null;

        switch($this->module_identifier)
        {
            case PostContractClaim::TYPE_VARIATION_ORDER:
                $route = route('topManagementVerifiers.contractManagement.variationOrder.index', array( $project->id ));
                break;
            case PostContractClaim::TYPE_CLAIM_CERTIFICATE:
                $route = route('topManagementVerifiers.contractManagement.claimCertificate.index', array( $project->id ));
                break;
        }

        return $route;
    }

    public static function verifyAsSubstitute(Project $project, User $user, $moduleIdentifier, $objectId, $approve, User $substitute)
    {
        $record = static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('user_id', '=', $user->getBsUser()->id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->where('object_id', '=', $objectId)
            ->first();

        $record->approved      = $approve;
        $record->substitute_id = $substitute->getBsUser()->id;
        $record->verified_at   = Carbon::now();

        $success = $record->save();

        // Set start_at for next verifier.
        if( $approve && $nextVerifierRecord = self::getCurrentVerifierRecord($project, $moduleIdentifier, $objectId) )
        {
            $nextVerifierRecord->start_at = Carbon::now();
            $nextVerifierRecord->save();
        }

        return $success;
    }

    public static function getRecordList(Project $project, $moduleIdentifier, $withTrashed = false)
    {
        $records = static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->orderBy('sequence_number', 'asc')
            ->get();

        if( $withTrashed )
        {
            $pastRecords = static::withTrashed()->where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
                ->where('module_identifier', '=', $moduleIdentifier)
                ->whereNotNull('approved')
                ->orderBy('verified_at', 'asc')
                ->get();

            $records = $pastRecords->merge($records);
        }

        return $records;
    }

    public static function getObjectRecordList(Project $project, $moduleIdentifier, $objectId, $withTrashed = false)
    {
        $records = static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->where('object_id', '=', $objectId)
            ->orderBy('sequence_number', 'asc')
            ->get();

        if( $withTrashed )
        {
            $pastRecords = static::withTrashed()->where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
                ->where('module_identifier', '=', $moduleIdentifier)
                ->where('object_id', '=', $objectId)
                ->whereNotNull('approved')
                ->orderBy('verified_at', 'asc')
                ->get();

            $records = $pastRecords->merge($records);
        }

        return $records;
    }

    public static function isCurrentVerifier(Project $project, User $user, $moduleIdentifier, $objectId)
    {
        if( ! $currentVerifier = self::getCurrentVerifier($project, $moduleIdentifier, $objectId) ) return false;

        return ( $user->id == $currentVerifier->id );
    }

    public static function getCurrentVerifier(Project $project, $moduleIdentifier, $objectId)
    {
        if( ! $record = self::getCurrentVerifierRecord($project, $moduleIdentifier, $objectId) ) return null;

        return $record->verifier;
    }

    public static function getCurrentVerifierRecord(Project $project, $moduleIdentifier, $objectId)
    {
        if( self::isApproved($project, $moduleIdentifier, $objectId) || self::isRejected($project, $moduleIdentifier, $objectId) ) return null;

        return static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->where('object_id', '=', $objectId)
            ->whereNull('approved')
            ->orderBy('sequence_number', 'asc')
            ->first();
    }

    public static function isPending(Project $project, $moduleIdentifier, $objectId)
    {
        return ( self::getCurrentVerifierRecord($project, $moduleIdentifier, $objectId) ? true : false );
    }

    public static function isApproved(Project $project, $moduleIdentifier, $objectId)
    {
        $rejectedRecords = self::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->where('object_id', '=', $objectId)
            ->get()
            ->reject(function($record)
            {
                return ( $record->approved );
            });

        return ( $rejectedRecords->isEmpty() );
    }

    public static function isRejected(Project $project, $moduleIdentifier, $objectId)
    {
        $record = static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->where('object_id', '=', $objectId)
            ->where('approved', '=', false)
            ->first();

        return ( $record ? true : false );
    }

    public static function getPendingRecordsByModule(User $user, bool $onlyCurrent, Project $project = null)
    {
        $pendingVerifierRecords = [];
        
        $records = static::where('user_id', '=', $user->getBsUser()->id);

        if( $project && $project->getBsProjectMainInformation()) $records->where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id);

        $records = $records->whereNull('approved')
            ->orderBy('project_structure_id', 'asc')
            ->orderBy('module_identifier', 'asc')
            ->orderBy('object_id', 'asc')
            ->orderBy('sequence_number', 'asc')
            ->get();

        foreach($records as $record)
        {
            if( ! $project = $record->getProject() ) continue;

            if( ! $record->object ) continue;

            if ( ! self::isPending($project, $record->module_identifier, $record->object->id) ) continue;

            $isCurrentVerifier = self::isCurrentVerifier($project, $user, $record->module_identifier, $record->object->id);

            if( $onlyCurrent && $project && ( ! $isCurrentVerifier ) ) continue;

            if( ! array_key_exists($record->module_identifier, $pendingVerifierRecords) )
            {
                $pendingVerifierRecords[ $record->module_identifier ] = array();
            }

            $record['is_future_task'] = ! $isCurrentVerifier;

            $pendingVerifierRecords[ $record->module_identifier ][ $record->id ] = $record;
        }

        return $pendingVerifierRecords;
    }

    public static function getPendingRecords(User $user, $onlyCurrent, Project $project = null)
    {
        $pendingVerifierRecords = new Collection();

        foreach(self::getPendingRecordsByModule($user, $onlyCurrent, $project) as $moduleIdentifier => $moduleRecords)
        {
            $pendingVerifierRecords = $pendingVerifierRecords->merge($moduleRecords);
        }

        return $pendingVerifierRecords;
    }

    public static function getModuleClass($moduleIdentifier)
    {
        switch($moduleIdentifier)
        {
            case PostContractClaim::TYPE_VARIATION_ORDER:
                $class = new VariationOrder;
                break;
            case PostContractClaim::TYPE_CLAIM_CERTIFICATE:
                $class = new ClaimCertificate;
                break;
            case PostContractClaim::TYPE_WATER_DEPOSIT:
            case PostContractClaim::TYPE_DEPOSIT:
            case PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM:
            case PostContractClaim::TYPE_PURCHASE_ON_BEHALF:
            case PostContractClaim::TYPE_ADVANCED_PAYMENT:
            case PostContractClaim::TYPE_WORK_ON_BEHALF:
            case PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE:
            case PostContractClaim::TYPE_PENALTY:
            case PostContractClaim::TYPE_PERMIT:
            case PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                $class = new PostContractClaim;
                break;
            default:
                throw new \Exception('Invalid module');
        }

        return $class;
    }

    protected static function getNotificationRecipients($project, $moduleIdentifier, $objectId)
    {
        $allReviewers = array();
        $recipients   = array();

        foreach(self::getObjectRecordList($project, $moduleIdentifier, $objectId)->lists('user_id') as $bsUserId)
        {
            $eProjectUser                      = \PCK\Buildspace\User::find($bsUserId)->Profile->getEProjectUser();
            $allReviewers[ $eProjectUser->id ] = $eProjectUser;
        }

        if( self::isApproved($project, $moduleIdentifier, $objectId) )
        {
            $recipients = $allReviewers;
        }
        elseif( self::isRejected($project, $moduleIdentifier, $objectId) )
        {
            $recipients = $allReviewers;
        }
        elseif( self::isPending($project, $moduleIdentifier, $objectId) )
        {
            $recipients = array( self::getCurrentVerifier($project, $moduleIdentifier, $objectId) );
        }

        return $recipients;
    }

    public static function sendNotifications(Project $project, $moduleIdentifier, $objectId)
    {
        $class = self::getModuleClass($moduleIdentifier);

        $object = $class::find($objectId);

        $route = null;

        if( $firstRecord = self::getObjectRecordList($project, $moduleIdentifier, $objectId)->first() )
        {
            $route = $firstRecord->getRoute();
        }

        $view = null;

        if( self::isApproved($project, $moduleIdentifier, $objectId) )
        {
            $view = "postContractClaim.approved";
        }
        elseif( self::isRejected($project, $moduleIdentifier, $objectId) )
        {
            $view = "postContractClaim.rejected";
        }
        elseif( self::isPending($project, $moduleIdentifier, $objectId) )
        {
            $view = "postContractClaim.review";
        }

        $recipients = self::getNotificationRecipients($project, $moduleIdentifier, $objectId);

        if($view)
        {
            foreach($recipients as $recipient)
            {
                $isVerifierWithoutProjectAccess = is_null($recipient->getAssignedCompany($project)) && $recipient->isTopManagementVerifier();

                $viewData = array(
                    'project'         => $project,
                    'parentProject'   => $project->parentProject,
                    'moduleName'      => PostContractClaim::getModuleName($moduleIdentifier),
                    'itemDescription' => $object->displayDescription,
                    'toRoute'         => $isVerifierWithoutProjectAccess ? $firstRecord->getRouteByRecipient($recipient) : $route,
                    'recipientLocale' => $recipient->settings->language->code,
                );

                Mailer::queue(null, "notifications.email.{$view}", $recipient, trans('email.eClaimNotification'), $viewData);
            }
        }

        self::notifyFinanceModuleUsers($project, $moduleIdentifier, $objectId);
    }

    public static function notifyFinanceModuleUsers(Project $project, $moduleIdentifier, $objectId)
    {
        if( $moduleIdentifier != PostContractClaim::TYPE_CLAIM_CERTIFICATE ) return;

        if( ! self::isApproved($project, $moduleIdentifier, $objectId) ) return;

        $class = self::getModuleClass($moduleIdentifier);

        $object = $class::find($objectId);

        $view = null;

        if( self::isApproved($project, $moduleIdentifier, $objectId) )
        {
            $view = "postContractClaim.approved";
        }
        elseif( self::isRejected($project, $moduleIdentifier, $objectId) )
        {
            $view = "postContractClaim.rejected";
        }
        elseif( self::isPending($project, $moduleIdentifier, $objectId) )
        {
            $view = "postContractClaim.review";
        }

        $recipients = [];
        $projectSubsidiary = $project->subsidiary;
        $financeModuleEditors = ModulePermission::getEditorList(ModulePermission::MODULE_ID_FINANCE);

        foreach($financeModuleEditors as $financeModuleEditor)
        {
            $financeUserAssignedSubsidiaryIds = $financeModuleEditor->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

            if(count($financeUserAssignedSubsidiaryIds) == 0) continue;

            if(in_array($projectSubsidiary->id, $financeUserAssignedSubsidiaryIds))
            {
                array_push($recipients, $financeModuleEditor);
            }
        }

        if($view)
        {
            foreach($recipients as $user)
            {
                $viewData = array(
                    'project'         => $project,
                    'parentProject'   => $project->parentProject,
                    'moduleName'      => PostContractClaim::getModuleName($moduleIdentifier),
                    'itemDescription' => $object->displayDescription,
                    'toRoute'         => route('finance.claim-certificate'),
                    'recipientLocale' => $user->settings->language->code,
                );

                Mailer::queue(null, "notifications.email.{$view}", $user, trans('email.eClaimNotification'), $viewData);
            }
        }
    }

}