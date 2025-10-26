<?php namespace PCK\IndonesiaCivilContract\ArchitectInstruction;

use Carbon\Carbon;
use Illuminate\Events\Dispatcher;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\ClauseItems\ClauseItem;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;
use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;
use PCK\Verifier\Verifier;

class ArchitectInstructionRepository extends BaseModuleRepository {

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

        $records = ArchitectInstruction::where('project_id', '=', $project->id)
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

    public function find(Project $project, $aiId)
    {
        return ArchitectInstruction::where('id', '=', $aiId)
            ->where('project_id', '=', $project->id)
            ->first();
    }

    public function findWithMessages(Project $project, $aiId)
    {
        return ArchitectInstruction::with('attachedClauses', 'attachments.file')
            ->where('id', '=', $aiId)
            ->where('project_id', '=', $project->id)
            ->first();
    }

    public function selectList(Project $project)
    {
        $results = ArchitectInstruction::where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->where('status', '=', ArchitectInstruction::STATUS_SUBMITTED)
            ->lists('reference', 'id');

        $data[ -1 ] = trans('architectInstructions.notAssociatedWithAnyArchitectInstruction');

        foreach($results as $key => $result)
        {
            $data[ $key ] = $result;
        }

        return $data;
    }

    public function add(Project $project, array $inputs)
    {
        $user = \Confide::user();
        $ai   = new ArchitectInstruction();

        $ai->project_id         = $project->id;
        $ai->user_id            = $user->id;
        $ai->reference          = $inputs['reference'];
        $ai->instruction        = $inputs['instruction'];
        $ai->deadline_to_comply = empty( $inputs['deadline_to_comply'] ) ? null : date('Y-m-d', strtotime($inputs['deadline_to_comply']));
        $ai->status             = ArchitectInstruction::STATUS_DRAFT;

        if( isset( $inputs['issue_ai'] ) )
        {
            $ai->status     = ArchitectInstruction::STATUS_PENDING;
            $ai->created_at = Carbon::now();
        }

        $ai->save();

        $ai->requestsForInformation()->sync($inputs['rfi'] ?? array());
        $this->saveClauses($ai, $inputs);
        $this->saveAttachments($ai, $inputs);

        Verifier::setVerifiers($inputs['verifiers'] ?? array(), $ai);

        return $ai;
    }

    public function update(User $user, ArchitectInstruction $ai, array $inputs)
    {
        $ai->reference          = $inputs['reference'];
        $ai->instruction        = $inputs['instruction'];
        $ai->user_id            = $user->id;
        $ai->deadline_to_comply = empty( $inputs['deadline_to_comply'] ) ? null : date('Y-m-d', strtotime($inputs['deadline_to_comply']));

        if( isset( $inputs['issue_ai'] ) )
        {
            $ai->status     = ArchitectInstruction::STATUS_PENDING;
            $ai->created_at = Carbon::now();
        }

        $ai->save();

        $ai->requestsForInformation()->sync($inputs['rfi'] ?? array());
        $this->saveClauses($ai, $inputs);
        $this->saveAttachments($ai, $inputs);

        Verifier::setVerifiers($inputs['verifiers'] ?? array(), $ai);

        return $ai;
    }

    public function delete(ArchitectInstruction $ai)
    {
        if( $ai->status != ArchitectInstruction::STATUS_DRAFT )
        {
            throw new \InvalidArgumentException('Only AIs that are in Draft can be deleted.');
        }

        return $ai->delete();
    }

    private function saveClauses(ArchitectInstruction $ai, array $inputs)
    {
        $ai->attachedClauses()->delete();

        if( isset( $inputs['selected_clauses'] ) )
        {
            foreach(ClauseItem::whereIn('id', $inputs['selected_clauses'])->get() as $clauseItem)
            {
                $attachedClauseItem = new AttachedClauseItem(array(
                    'no'          => $clauseItem->no,
                    'description' => $clauseItem->description,
                    'priority'    => $clauseItem->priority,
                ));

                $attachedClauseItem->attachable()->associate($ai);
                $attachedClauseItem->save();
            }
        }

        return $ai;
    }

    public function submitResponse(ArchitectInstruction $architectInstruction, User $user, $inputs)
    {
        $response = new ContractualClaimResponse(array(
            'user_id'  => $user->id,
            'subject'  => $inputs['subject'],
            'content'  => $inputs['content'],
            'sequence' => $architectInstruction->getNextResponseSequenceNumber(),
            'type'     => ContractualClaimResponse::TYPE_PLAIN,
        ));

        $response->object()->associate($architectInstruction);

        $success = $response->save();

        $this->saveAttachments($response, $inputs);

        $this->sendResponseNotifications($architectInstruction->project, $architectInstruction);

        return $success;
    }

    protected function sendResponseNotifications(Project $project, ArchitectInstruction $ai)
    {
        if( $ai->contractorsTurn() )
        {
            $recipients = $project->getSelectedContractor()->getActiveUsers()->toArray() ?? array();
        }
        else
        {
            $recipients = ProjectModulePermission::getAssigned($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_ARCHITECT_INSTRUCTION)->toArray();
        }

        $this->sendEmailNotificationByUsers($project, $ai, $recipients, 'architect_instruction', 'indonesiaCivilContract.architectInstructions.show');
        $this->sendSystemNotificationByUsers($project, $ai, $recipients, 'architect_instruction', 'indonesiaCivilContract.architectInstructions.show');
    }

}