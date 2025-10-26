<?php namespace PCK\IndonesiaCivilContract\LossAndExpense;

use Carbon\Carbon;
use Illuminate\Events\Dispatcher;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;
use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;

class LossAndExpenseRepository extends BaseModuleRepository {

    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function all(Project $project)
    {
        return self::getAll($project);
    }

    public static function getAll(Project $project)
    {
        $user = \Confide::user();

        $records = LossAndExpense::where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->get();

        return $records->filter(function($item) use ($user)
        {
            return $item->isVisible($user);
        });
    }

    public static function getCount(Project $project)
    {
        return self::getAll($project)->count();
    }

    public function findWithMessages(Project $project, $leId)
    {
        return LossAndExpense::with('attachedClauses', 'attachments.file')
            ->where('id', '=', $leId)
            ->where('project_id', '=', $project->id)
            ->first();
    }

    public function add(Project $project, array $inputs)
    {
        $user = \Confide::user();

        $le = new LossAndExpense(array(
            'project_id'                     => $project->id,
            'user_id'                        => $user->id,
            'indonesia_civil_contract_ai_id' => ( $inputs['indonesia_civil_contract_ai_id'] > 0 ) ? $inputs['indonesia_civil_contract_ai_id'] : null,
            'reference'                      => $inputs['reference'],
            'subject'                        => $inputs['subject'],
            'details'                        => $inputs['details'],
            'status'                         => LossAndExpense::STATUS_DRAFT,
            'claim_amount'                   => $inputs['claim_amount'],
        ));

        if( isset( $inputs['issue'] ) )
        {
            $le->status     = LossAndExpense::STATUS_SUBMITTED;
            $le->created_at = Carbon::now();
        }

        $le->save();

        $le->earlyWarnings()->sync($inputs['early_warnings'] ?? array());

        AttachedClauseItem::syncClauses($le, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($le, $inputs);

        if( $le->status == LossAndExpense::STATUS_SUBMITTED )
        {
            $users = ProjectModulePermission::getAssigned($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_LOSS_AND_EXPENSES)->toArray();

            $this->sendEmailNotificationByUsers($project, $le, $users, 'loss_and_or_expense', 'indonesiaCivilContract.lossAndExpenses.show');
            $this->sendSystemNotificationByUsers($project, $le, $users, 'loss_and_or_expense', 'indonesiaCivilContract.lossAndExpenses.show');
        }

        return $le;
    }

    public function update(User $user, LossAndExpense $le, array $inputs)
    {
        $le->reference                      = $inputs['reference'];
        $le->subject                        = $inputs['subject'];
        $le->details                        = $inputs['details'];
        $le->claim_amount                   = $inputs['claim_amount'];
        $le->indonesia_civil_contract_ai_id = ( $inputs['indonesia_civil_contract_ai_id'] > 0 ) ? $inputs['indonesia_civil_contract_ai_id'] : null;
        $le->user_id                        = $user->id;

        if( isset( $inputs['issue'] ) )
        {
            $le->status     = LossAndExpense::STATUS_SUBMITTED;
            $le->created_at = Carbon::now();
        }

        $le->save();

        $le->earlyWarnings()->sync($inputs['early_warnings'] ?? array());

        AttachedClauseItem::syncClauses($le, $inputs['selected_clauses'] ?? array());
        $this->saveAttachments($le, $inputs);

        if( $le->status == LossAndExpense::STATUS_SUBMITTED )
        {
            $users = ProjectModulePermission::getAssigned($le->project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_LOSS_AND_EXPENSES)->toArray();

            $this->sendEmailNotificationByUsers($le->project, $le, $users, 'loss_and_or_expense', 'indonesiaCivilContract.lossAndExpenses.show');
            $this->sendSystemNotificationByUsers($le->project, $le, $users, 'loss_and_or_expense', 'indonesiaCivilContract.lossAndExpenses.show');
        }

        return $le;
    }

    public function delete(LossAndExpense $le)
    {
        if( $le->status != LossAndExpense::STATUS_DRAFT )
        {
            throw new \InvalidArgumentException('Only LEs that are in Draft can be deleted.');
        }

        return $le->delete();
    }

    public function submitResponse(LossAndExpense $lossAndExpenses, User $user, $inputs)
    {
        $response = new ContractualClaimResponse(array(
            'user_id'  => $user->id,
            'subject'  => $inputs['subject'],
            'content'  => $inputs['content'],
            'sequence' => $lossAndExpenses->getNextResponseSequenceNumber(),
            'type'     => $inputs['type'],
        ));

        if( $inputs['type'] == ContractualClaimResponse::TYPE_GRANT )
        {
            $lossAndExpenses->status  = LossAndExpense::STATUS_GRANTED;
            $response->proposed_value = $inputs['proposed_value'];
        }
        elseif( $inputs['type'] == ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE )
        {
            $lossAndExpenses->status = LossAndExpense::STATUS_APPROVED;
        }
        elseif( $inputs['type'] == ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE )
        {
            $lossAndExpenses->status = LossAndExpense::STATUS_REJECTED;
        }

        if( $lossAndExpenses->isDirty() ) $lossAndExpenses->save();

        $response->object()->associate($lossAndExpenses);

        $success = $response->save();

        $this->saveAttachments($response, $inputs);

        $this->sendResponseNotifications($lossAndExpenses->project, $lossAndExpenses);

        return $success;
    }

    protected function sendResponseNotifications(Project $project, LossAndExpense $le)
    {
        if( $le->contractorsTurn() )
        {
            $recipients = $project->getSelectedContractor()->getActiveUsers()->toArray() ?? array();
        }
        else
        {
            $recipients = ProjectModulePermission::getAssigned($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_LOSS_AND_EXPENSES)->toArray();
        }

        $this->sendEmailNotificationByUsers($project, $le, $recipients, 'loss_and_or_expense', 'indonesiaCivilContract.lossAndExpenses.show');
        $this->sendSystemNotificationByUsers($project, $le, $recipients, 'loss_and_or_expense', 'indonesiaCivilContract.lossAndExpenses.show');
    }

}