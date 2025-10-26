<?php namespace PCK\ExtensionOfTimes;

use Carbon\Carbon;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\Users\User;
use PCK\Projects\Project;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;

class ExtensionOfTimeRepository extends BaseModuleRepository {

    private $eot;

    protected $events;

    public function __construct(ExtensionOfTime $eot, Dispatcher $events)
    {
        $this->eot    = $eot;
        $this->events = $events;
    }

    public function all(Project $project)
    {
        $user = \Confide::user();

        $query = $this->eot
            ->where('project_id', '=', $project->id)
            ->orderBy('id', 'desc');

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', ExtensionOfTime::DRAFT);
        }

        return $query->get(array(
            'id', 'commencement_date_of_event', 'subject', 'detailed_elaborations',
            'days_claimed', 'days_granted', 'initial_estimate_of_eot', 'status',
            'created_at', 'updated_at'
        ));
    }

    public static function getEOTCount(Project $project)
    {
        $user = \Confide::user();

        $eotObj = new ExtensionOfTime();

        $query = \DB::table($eotObj->getTable())->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', ExtensionOfTime::DRAFT);
        }

        return $query->count();
    }

    public function find(Project $project, $eotId)
    {
        $user = \Confide::user();

        $query = $this->eot->where('id', '=', $eotId)
            ->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', ExtensionOfTime::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function findWithMessages(Project $project, $eotId)
    {
        $user = \Confide::user();

        $query = $this->eot->with(
            'attachedClauses', 'attachments.file',
            'firstLevelMessages.createdBy', 'firstLevelMessages.attachments.file',
            'eotContractorConfirmDelay.createdBy', 'eotContractorConfirmDelay.attachments.file',
            'secondLevelMessages.createdBy', 'secondLevelMessages.attachments.file',
            'extensionOfTimeClaim.createdBy', 'extensionOfTimeClaim.attachments.file'
        )
            ->where('id', '=', $eotId)
            ->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', ExtensionOfTime::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function add(Project $project, User $user, array $inputs)
    {
        $eot = $this->eot;

        $eot->project_id                 = $project->id;
        $eot->architect_instruction_id   = ( $inputs['architect_instruction_id'] > 0 ) ? $inputs['architect_instruction_id'] : null;
        $eot->created_by                 = $user->id;
        $eot->commencement_date_of_event = $inputs['commencement_date_of_event'];
        $eot->subject                    = $inputs['subject'];
        $eot->detailed_elaborations      = $inputs['detailed_elaborations'];
        $eot->initial_estimate_of_eot    = $inputs['initial_estimate_of_eot'];
        $eot->status                     = ExtensionOfTime::DRAFT;

        if( $user->isEditor($project) and isset( $inputs['issue_eot'] ) )
        {
            $eot->status     = ExtensionOfTime::PENDING;
            $eot->created_at = Carbon::now();
        }

        $eot = $this->save($eot);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($eot, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($eot, $inputs);

        if( $eot->status == ExtensionOfTime::PENDING_TEXT )
        {
            $this->sendEmailNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show');
            $this->sendSystemNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show');
        }

        return $eot;
    }

    public function update(ExtensionOfTime $eot, User $user, array $inputs)
    {
        $eot->architect_instruction_id   = ( $inputs['architect_instruction_id'] > 0 ) ? $inputs['architect_instruction_id'] : null;
        $eot->created_by                 = $user->id;
        $eot->commencement_date_of_event = $inputs['commencement_date_of_event'];
        $eot->subject                    = $inputs['subject'];
        $eot->detailed_elaborations      = $inputs['detailed_elaborations'];
        $eot->initial_estimate_of_eot    = $inputs['initial_estimate_of_eot'];
        $eot->status                     = ExtensionOfTime::DRAFT;

        if( $user->isEditor($eot->project) and isset( $inputs['issue_eot'] ) )
        {
            $eot->status     = ExtensionOfTime::PENDING;
            $eot->created_at = Carbon::now();
        }

        $eot = $this->save($eot);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($eot, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($eot, $inputs);

        if( $eot->status == ExtensionOfTime::PENDING_TEXT )
        {
            $this->sendEmailNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show');
            $this->sendSystemNotification($eot->project, $eot, [ Role::INSTRUCTION_ISSUER ], 'extension_of_time', 'eot.show');
        }

        return $eot;
    }

    public function delete(ExtensionOfTime $eot)
    {
        // will add checking and see other than DRAFT's status, cannot be deleted.
        if( $eot->status != ExtensionOfTime::DRAFT_TEXT )
        {
            throw new \InvalidArgumentException('Only can delete EOT that is currently in Draft\'s mode.');
        }

        return $eot->delete();
    }

    public function save(ExtensionOfTime $eot)
    {
        $eot->save();

        return $eot;
    }

}