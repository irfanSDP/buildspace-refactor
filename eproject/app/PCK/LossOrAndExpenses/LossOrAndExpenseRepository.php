<?php namespace PCK\LossOrAndExpenses;

use Carbon\Carbon;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\Users\User;
use PCK\Projects\Project;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;

class LossOrAndExpenseRepository extends BaseModuleRepository {

    private $loe;

    protected $events;

    public function __construct(LossOrAndExpense $loe, Dispatcher $events)
    {
        $this->loe    = $loe;
        $this->events = $events;
    }

    public function all(Project $project)
    {
        $user = \Confide::user();

        $query = $this->loe->where('project_id', '=', $project->id)->orderBy('id', 'desc');

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', LossOrAndExpense::DRAFT);
        }

        return $query->get(array(
            'id', 'commencement_date_of_event', 'subject', 'detailed_elaborations',
            'amount_claimed', 'amount_granted', 'initial_estimate_of_claim', 'status',
            'created_at', 'updated_at'
        ));
    }

    public static function getLOACount(Project $project)
    {
        $user = \Confide::user();

        $loaObj = new LossOrAndExpense();

        $query = \DB::table($loaObj->getTable())->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', LossOrAndExpense::DRAFT);
        }

        return $query->count();
    }

    public function find(Project $project, $loeId)
    {
        $user = \Confide::user();

        $query = $this->loe->where('id', '=', $loeId)->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', LossOrAndExpense::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function findWithMessages(Project $project, $loeId)
    {
        $user = \Confide::user();

        $query = $this->loe->with(
            'attachedClauses', 'attachments.file',
            'firstLevelMessages.createdBy', 'firstLevelMessages.attachments.file',
            'contractorConfirmDelay.createdBy', 'contractorConfirmDelay.attachments.file',
            'secondLevelMessages.createdBy', 'secondLevelMessages.attachments.file',
            'lossOrAndExpenseClaim.createdBy', 'lossOrAndExpenseClaim.attachments.file',
            'thirdLevelMessages.createdBy', 'thirdLevelMessages.attachments.file',
            'fourthLevelMessages.createdBy', 'fourthLevelMessages.attachments.file'
        )
            ->where('id', '=', $loeId)
            ->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            $query->where('status', '<>', LossOrAndExpense::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function add(Project $project, User $user, array $inputs)
    {
        $loe = $this->loe;

        $loe->project_id                 = $project->id;
        $loe->architect_instruction_id   = ( $inputs['architect_instruction_id'] > 0 ) ? $inputs['architect_instruction_id'] : null;
        $loe->created_by                 = $user->id;
        $loe->commencement_date_of_event = $inputs['commencement_date_of_event'];
        $loe->subject                    = $inputs['subject'];
        $loe->detailed_elaborations      = $inputs['detailed_elaborations'];
        $loe->initial_estimate_of_claim  = $inputs['initial_estimate_of_claim'];
        $loe->status                     = LossOrAndExpense::DRAFT;

        if( $user->isEditor($project) and isset( $inputs['issue_loe'] ) )
        {
            $loe->status     = LossOrAndExpense::PENDING;
            $loe->created_at = Carbon::now();
        }

        $loe = $this->save($loe);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($loe, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($loe, $inputs);

        if( $loe->status == LossOrAndExpense::PENDING_TEXT )
        {
            $this->sendEmailNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER ], 'loss_and_or_expense', 'loe.show');
            $this->sendSystemNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER ], 'loss_and_or_expense', 'loe.show');
        }

        return $loe;
    }

    public function update(LossOrAndExpense $loe, User $user, array $inputs)
    {
        $loe->architect_instruction_id   = ( $inputs['architect_instruction_id'] > 0 ) ? $inputs['architect_instruction_id'] : null;
        $loe->created_by                 = $user->id;
        $loe->commencement_date_of_event = $inputs['commencement_date_of_event'];
        $loe->subject                    = $inputs['subject'];
        $loe->detailed_elaborations      = $inputs['detailed_elaborations'];
        $loe->initial_estimate_of_claim  = $inputs['initial_estimate_of_claim'];
        $loe->status                     = LossOrAndExpense::DRAFT;

        if( $user->isEditor($loe->project) and isset( $inputs['issue_loe'] ) )
        {
            $loe->status     = LossOrAndExpense::PENDING;
            $loe->created_at = Carbon::now();
        }

        $loe = $this->save($loe);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($loe, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($loe, $inputs);

        if( $loe->status == LossOrAndExpense::PENDING_TEXT )
        {
            $this->sendEmailNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER ], 'loss_and_or_expense', 'loe.show');
            $this->sendSystemNotification($loe->project, $loe, [ Role::INSTRUCTION_ISSUER ], 'loss_and_or_expense', 'loe.show');
        }

        return $loe;
    }

    public function delete(LossOrAndExpense $loe)
    {
        // will add checking and see other than DRAFT's status, cannot be deleted.
        if( $loe->status != LossOrAndExpense::DRAFT_TEXT )
        {
            throw new \InvalidArgumentException('Only can delete L and E that is currently in Draft\'s mode.');
        }

        return $loe->delete();
    }

    private function save(LossOrAndExpense $instance)
    {
        $instance->save();

        return $instance;
    }

}