<?php namespace PCK\AdditionalExpenses;

use Carbon\Carbon;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\Users\User;
use PCK\Projects\Project;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;

class AdditionalExpenseRepository extends BaseModuleRepository {

    private $additionalExpense;

    protected $events;

    public function __construct(AdditionalExpense $additionalExpense, Dispatcher $events)
    {
        $this->additionalExpense = $additionalExpense;
        $this->events            = $events;
    }

    public function all(Project $project)
    {
        $user = \Confide::user();

        $query = $this->additionalExpense->where('project_id', '=', $project->id)
            ->orderBy('id', 'desc');

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', AdditionalExpense::DRAFT);
        }

        return $query->get(array(
            'id', 'commencement_date_of_event', 'subject', 'detailed_elaborations',
            'amount_claimed', 'amount_granted', 'initial_estimate_of_claim', 'status',
            'created_at', 'updated_at'
        ));
    }

    public static function getAECount(Project $project)
    {
        $user = \Confide::user();

        $aeObj = new AdditionalExpense();

        $query = \DB::table($aeObj->getTable())->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', AdditionalExpense::DRAFT);
        }

        return $query->count();
    }

    public function find(Project $project, $aeId)
    {
        $user = \Confide::user();

        $query = $this->additionalExpense->where('id', '=', $aeId)
            ->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', AdditionalExpense::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function findWithMessages(Project $project, $aeId)
    {
        $user = \Confide::user();

        $query = $this->additionalExpense->with(
            'attachedClauses', 'attachments.file',
            'firstLevelMessages.createdBy', 'firstLevelMessages.attachments.file',
            'contractorConfirmDelay.createdBy', 'contractorConfirmDelay.attachments.file',
            'secondLevelMessages.createdBy', 'secondLevelMessages.attachments.file',
            'additionalExpenseClaim.createdBy', 'additionalExpenseClaim.attachments.file',
            'thirdLevelMessages.createdBy', 'thirdLevelMessages.attachments.file',
            'fourthLevelMessages.createdBy', 'fourthLevelMessages.attachments.file'
        )
            ->where('id', '=', $aeId)
            ->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', AdditionalExpense::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function add(Project $project, User $user, array $inputs)
    {
        $ae = $this->additionalExpense;

        $ae->project_id                 = $project->id;
        $ae->architect_instruction_id   = ( $inputs['architect_instruction_id'] > 0 ) ? $inputs['architect_instruction_id'] : null;
        $ae->created_by                 = $user->id;
        $ae->commencement_date_of_event = $inputs['commencement_date_of_event'];
        $ae->subject                    = $inputs['subject'];
        $ae->detailed_elaborations      = $inputs['detailed_elaborations'];
        $ae->initial_estimate_of_claim  = $inputs['initial_estimate_of_claim'];
        $ae->status                     = AdditionalExpense::DRAFT;

        if( $user->isEditor($project) and isset( $inputs['issue_ae'] ) )
        {
            $ae->status     = AdditionalExpense::PENDING;
            $ae->created_at = Carbon::now();
        }

        $ae = $this->save($ae);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($ae, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($ae, $inputs);

        if( $ae->status == AdditionalExpense::PENDING_TEXT )
        {
            $this->sendEmailNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER ], 'additional_expense', 'ae.show');
            $this->sendSystemNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER ], 'additional_expense', 'ae.show');
        }

        return $ae;
    }

    public function update(AdditionalExpense $ae, User $user, array $inputs)
    {
        $ae->architect_instruction_id   = ( $inputs['architect_instruction_id'] > 0 ) ? $inputs['architect_instruction_id'] : null;
        $ae->created_by                 = $user->id;
        $ae->commencement_date_of_event = $inputs['commencement_date_of_event'];
        $ae->subject                    = $inputs['subject'];
        $ae->detailed_elaborations      = $inputs['detailed_elaborations'];
        $ae->initial_estimate_of_claim  = $inputs['initial_estimate_of_claim'];
        $ae->status                     = AdditionalExpense::DRAFT;

        if( $user->isEditor($ae->project) and isset( $inputs['issue_ae'] ) )
        {
            $ae->status     = AdditionalExpense::PENDING;
            $ae->created_at = Carbon::now();
        }

        $ae = $this->save($ae);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($ae, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($ae, $inputs);

        if( $ae->status == AdditionalExpense::PENDING_TEXT )
        {
            $this->sendEmailNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER ], 'additional_expense', 'ae.show');
            $this->sendSystemNotification($ae->project, $ae, [ Role::INSTRUCTION_ISSUER ], 'additional_expense', 'ae.show');
        }

        return $ae;
    }

    public function delete(AdditionalExpense $ae)
    {
        // will add checking and see other than DRAFT's status, cannot be deleted.
        if( $ae->status != AdditionalExpense::DRAFT_TEXT )
        {
            throw new \InvalidArgumentException('Only can delete AE that is currently in Draft\'s mode.');
        }

        return $ae->delete();
    }

    private function save(AdditionalExpense $ae)
    {
        $ae->save();

        return $ae;
    }

}