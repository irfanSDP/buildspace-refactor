<?php namespace PCK\Buildspace;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\ModulePermission\ModulePermission;
use PCK\Helpers\Mailer;
use PCK\Notifications\SystemNotifier;
use PCK\Projects\Project;
use PCK\Buildspace\Project as BsProject;
use PCK\Users\User;
use PCK\Verifier\BaseVerifier;

class ContractManagementVerifier extends BaseVerifier {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_contract_management_verifiers';

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

    public static function verifyAsSubstitute(Project $project, User $user, $moduleIdentifier, $approve, User $substitute)
    {
        $record = static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('user_id', '=', $user->getBsUser()->id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->first();

        $record->approved      = $approve;
        $record->substitute_id = $substitute->getBsUser()->id;
        $record->verified_at   = Carbon::now();

        $success = $record->save();

        // Set start_at for next verifier.
        if( $approve && $nextVerifierRecord = self::getCurrentVerifierRecord($project, $moduleIdentifier) )
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

    public static function isCurrentVerifier(Project $project, User $user, $moduleIdentifier)
    {
        if( ! $currentVerifier = self::getCurrentVerifier($project, $moduleIdentifier) ) return false;

        return ( $user->id == $currentVerifier->id );
    }

    public static function getCurrentVerifier(Project $project, $moduleIdentifier)
    {
        if( ! $record = self::getCurrentVerifierRecord($project, $moduleIdentifier) ) return null;

        return $record->verifier;
    }

    public static function getCurrentVerifierRecord(Project $project, $moduleIdentifier)
    {
        if( self::isApproved($project, $moduleIdentifier) || self::isRejected($project, $moduleIdentifier) ) return null;

        return static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->whereNull('approved')
            ->orderBy('sequence_number', 'asc')
            ->first();
    }

    public static function isPending(Project $project, $moduleIdentifier)
    {
        return ( self::getCurrentVerifierRecord($project, $moduleIdentifier) ? true : false );
    }

    public static function isApproved(Project $project, $moduleIdentifier)
    {
        $rejectedRecords = self::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
            ->get()
            ->reject(function($record)
            {
                return ( $record->approved );
            });

        return ( $rejectedRecords->isEmpty() );
    }

    public static function isRejected(Project $project, $moduleIdentifier)
    {
        $record = static::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)
            ->where('module_identifier', '=', $moduleIdentifier)
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
            ->orderBy('sequence_number', 'asc')
            ->get();

        foreach($records as $record)
        {
            if( ! $project = $record->getProject() ) continue;

            if( ! self::isPending($project, $record->module_identifier) ) continue;

            $isCurrentVerifier = self::isCurrentVerifier($project, $user, $record->module_identifier);

            if( $onlyCurrent && ( ! $isCurrentVerifier ) ) continue;

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

    public function getProject()
    {
        if( $bsProject = BsProject::find($this->project_structure_id) ) return $bsProject->mainInformation->getEProjectProject();

        return null;
    }

    public function getObjectDescription()
    {
        return trans('contractManagement.publishToPostContract');
    }

    public function getModuleName()
    {
        return PostContractClaim::getModuleName($this->module_identifier);
    }

    public function getRoute()
    {
        $projectId = $this->getProject()->id;

        switch($this->module_identifier)
        {
            case PostContractClaim::TYPE_LETTER_OF_AWARD:
                $route = route('contractManagement.letterOfAward.index', array( $projectId ));
                break;
            default:
                throw new \Exception('Invalid module');
        }

        return $route;
    }

    public static function sendNotifications(Project $project, $moduleIdentifier)
    {
        $route = null;

        if( $firstRecord = self::getRecordList($project, $moduleIdentifier)->first() ) $route = $firstRecord->getRoute();

        $allReviewers = array();

        foreach(self::getRecordList($project, $moduleIdentifier)->lists('user_id') as $bsUserId)
        {
            $allReviewers[] = \PCK\Buildspace\User::find($bsUserId)->Profile->getEProjectUser();
        }

        $recipients = array();
        $view       = null;

        $formSubmitter = \PCK\Buildspace\User::find(NewPostContractFormInformation::where('project_structure_id', '=', $project->getBsProjectMainInformation()->project_structure_id)->first()->updated_by)->Profile->getEProjectUser();

        if( self::isApproved($project, $moduleIdentifier) )
        {
            $recipients = $allReviewers;
            $view       = "contractManagementVerifier.approved";

            $recipients[] = $formSubmitter;
        }
        elseif( self::isRejected($project, $moduleIdentifier) )
        {
            $recipients = $allReviewers;
            $view       = "contractManagementVerifier.rejected";

            $recipients[] = $formSubmitter;
        }
        elseif( self::isPending($project, $moduleIdentifier) )
        {
            $recipients = array( self::getCurrentVerifier($project, $moduleIdentifier) );
            $view       = "contractManagementVerifier.review";
        }

        $viewData = array(
            'project'       => $project,
            'parentProject' => $project->parentProject,
            'moduleName'    => PostContractClaim::getModuleName($moduleIdentifier),
            'toRoute'       => $route,
            'customText'    => (($moduleIdentifier == PostContractClaim::TYPE_LETTER_OF_AWARD) && self::isApproved($project, $moduleIdentifier)) ? trans('contractManagement.letterOfAwardApproved') : null,
        );

        if( $view ) Mailer::queueMultiple($recipients, null, "notifications.email.{$view}", trans('email.eProjectNotification'), $viewData);

        self::notifyFinanceModuleUsers($project, $moduleIdentifier, $route);
    }

    public static function notifyFinanceModuleUsers(Project $project, $moduleIdentifier, $route)
    {
        if( $moduleIdentifier != PostContractClaim::TYPE_LETTER_OF_AWARD ) return;

        if( ! self::isApproved($project, $moduleIdentifier) ) return;

        $view = "contractManagementVerifier.approved";
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
                    'project'       => $project,
                    'parentProject' => $project->parentProject,
                    'moduleName'    => PostContractClaim::getModuleName($moduleIdentifier),
                    'customText'    => ($moduleIdentifier == PostContractClaim::TYPE_LETTER_OF_AWARD) ? trans('contractManagement.letterOfAwardApproved') : null,
                );

                Mailer::queue(null, "notifications.email.{$view}", $user, trans('email.eClaimNotification'), $viewData);
            }
        }
    }
}