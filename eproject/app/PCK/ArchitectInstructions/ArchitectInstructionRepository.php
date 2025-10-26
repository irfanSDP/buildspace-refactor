<?php namespace PCK\ArchitectInstructions;

use Carbon\Carbon;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\Users\User;
use PCK\Projects\Project;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;

class ArchitectInstructionRepository extends BaseModuleRepository {

    private $ai;

    protected $events;

    public function __construct(ArchitectInstruction $ai, Dispatcher $events)
    {
        $this->ai     = $ai;
        $this->events = $events;
    }

    public function all(Project $project)
    {
        $user = \Confide::user();

        $query = $this->ai
            ->where('project_id', '=', $project->id)
            ->orderBy('id', 'desc');

        if( ! $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            $query->where('status', '<>', ArchitectInstruction::DRAFT);
        }

        return $query->get(array( 'id', 'reference', 'created_at', 'deadline_to_comply', 'status' ));
    }

    public static function getAICount(Project $project)
    {
        $user = \Confide::user();

        $architectInstructionObj = new ArchitectInstruction();

        $query = \DB::table($architectInstructionObj->getTable())->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            $query->where('status', '<>', ArchitectInstruction::DRAFT);
        }

        return $query->count();
    }

    public function getWithStatusNotDraft(Project $project)
    {
        return $this->ai->where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->where('status', '<>', ArchitectInstruction::DRAFT)
            ->get();
    }

    public function find(Project $project, $aiId)
    {
        $user = \Confide::user();

        $query = $this->ai->where('id', '=', $aiId)
            ->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            $query->where('status', '<>', ArchitectInstruction::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function findWithMessages(Project $project, $aiId)
    {
        $user = \Confide::user();

        $query = $this->ai->with(
            'attachedClauses', 'attachments.file',
            'messages.attachedClauses', 'messages.createdBy', 'messages.attachments.file',
            'thirdLevelMessages.createdBy', 'thirdLevelMessages.attachments.file'
        )
            ->where('id', '=', $aiId)
            ->where('project_id', '=', $project->id);

        if( ! $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            $query->where('status', '<>', ArchitectInstruction::DRAFT);
        }

        return $query->firstOrFail();
    }

    public function selectList(Project $project)
    {
        $results = $this->ai
            ->where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->where('status', '<>', ArchitectInstruction::DRAFT)
            ->lists('reference', 'id');

        $data[ -1 ] = 'Not Related to any AI';

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
        $ai->status             = ArchitectInstruction::DRAFT;

        if( $user->isEditor($project) and isset( $inputs['issue_ai'] ) )
        {
            $ai->status     = ArchitectInstruction::PENDING;
            $ai->created_at = Carbon::now();
        }

        $ai = $this->save($ai);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($ai, $inputs['selected_clauses'] ?? array());

        // we will be saving attachment as well if available
        $this->saveAttachments($ai, $inputs);

        // send notifications
        if( $ai->status == ArchitectInstruction::PENDING_TEXT )
        {
            $this->sendEmailNotification($ai->project, $ai, [ Role::CONTRACTOR ], 'architect_instruction', 'ai.show');
            $this->sendSystemNotification($ai->project, $ai, [ Role::CONTRACTOR ], 'architect_instruction', 'ai.show');
        }

        return $ai;
    }

    public function update(User $user, ArchitectInstruction $ai, array $inputs)
    {
        $ai->reference          = $inputs['reference'];
        $ai->instruction        = $inputs['instruction'];
        $ai->deadline_to_comply = empty( $inputs['deadline_to_comply'] ) ? null : date('Y-m-d', strtotime($inputs['deadline_to_comply']));

        if( $user->isEditor($ai->project) and isset( $inputs['issue_ai'] ) )
        {
            $ai->status     = ArchitectInstruction::PENDING;
            $ai->created_at = Carbon::now();
        }

        $ai = $this->save($ai);

        // we will be saving the clauses selected, if available
        AttachedClauseItem::syncClauses($ai, $inputs['selected_clauses'] ?? array());

        // we will be saving attachment as well if available
        $this->saveAttachments($ai, $inputs);

        // send notifications
        if( $ai->status == ArchitectInstruction::PENDING_TEXT )
        {
            $this->sendEmailNotification($ai->project, $ai, [ Role::CONTRACTOR ], 'architect_instruction', 'ai.show');
            $this->sendSystemNotification($ai->project, $ai, [ Role::CONTRACTOR ], 'architect_instruction', 'ai.show');
        }

        return $ai;
    }

    public function delete(ArchitectInstruction $ai)
    {
        // will add checking and see other than DRAFT's status, cannot be deleted.
        if( $ai->status != ArchitectInstruction::DRAFT_TEXT )
        {
            throw new \InvalidArgumentException('Only can delete AI that is currently in Draft\'s mode.');
        }

        return $ai->delete();
    }

    public function save(ArchitectInstruction $ai)
    {
        $ai->save();

        return $ai;
    }

    public function calculateDeadLine(ArchitectInstruction $ai, $days = 0)
    {
        return self::calculateDeadlineToSubmitNoticeToClaim($ai->project, $ai->create_at, $days);
    }

    public static function calculateDeadlineToSubmitNoticeToClaim(Project $project, $startDate, $claimDuration)
    {
        $calendarRepo = \App::make('PCK\Calendars\CalendarRepository');

        return $calendarRepo->calculateFinalDate($project, $startDate, $claimDuration);
    }

}