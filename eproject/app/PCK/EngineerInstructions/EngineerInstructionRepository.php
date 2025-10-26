<?php namespace PCK\EngineerInstructions;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Projects\Project;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\Base\TimestampFormatterTrait;
use PCK\ContractGroups\Types\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EngineerInstructionRepository extends BaseModuleRepository {

    use TimestampFormatterTrait;

    private   $ei;
    protected $events;

    public function __construct(
        EngineerInstruction $ei,
        Dispatcher $events
    )
    {
        $this->ei     = $ei;
        $this->events = $events;
    }

    public function all(Project $project)
    {
        $user = \Confide::user();

        $company          = $user->getAssignedCompany($project);
        $currentUserGroup = ! $company ? false : $company->getContractGroup($project)->group;

        $results = $this->ei
            ->where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->get();

        foreach($results as $key => $result)
        {
            $recordUserGroup = $result->createdBy->getAssignedCompany($project, $result->created_at)->getContractGroup($project)->group;

            if( $result->status == EngineerInstruction::DRAFT_TEXT and $recordUserGroup != $currentUserGroup )
            {
                $results->forget($key);
            }

            unset( $result );
        }

        return $results;
    }

    public static function getEICount(Project $project)
    {
        $user = \Confide::user();

        $company          = $user->getAssignedCompany($project);
        $currentUserGroup = ! $company ? false : $company->getContractGroup($project)->group;

        $engineerInstructionObj = new EngineerInstruction();

        $results = $engineerInstructionObj
            ->where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->get();

        foreach($results as $key => $result)
        {
            $recordUserGroup = $result->createdBy->getAssignedCompany($project, $result->created_at)->getContractGroup($project)->group;

            if( $result->status == EngineerInstruction::DRAFT_TEXT and $recordUserGroup != $currentUserGroup )
            {
                $results->forget($key);
            }

            unset( $result );
        }

        return $results->count();
    }

    public function find(Project $project, $eiId)
    {
        $user = \Confide::user();

        $result = $this->ei
            ->with('project', 'createdBy', 'attachments.file')
            ->where('id', '=', $eiId)
            ->where('project_id', '=', $project->id)
            ->firstOrFail();

        $userCompany      = $user->getAssignedCompany($project);
        $currentUserGroup = ! $userCompany ? false : $userCompany->getContractGroup($project)->group;
        $recordUserGroup  = $result->createdBy->getAssignedCompany($project, $result->created_at)->getContractGroup($project)->group;

        if( $result->status == EngineerInstruction::DRAFT_TEXT and $recordUserGroup != $currentUserGroup )
        {
            throw new ModelNotFoundException;
        }

        return $result;
    }

    public function add(Project $project, User $user, array $inputs)
    {
        $ei = $this->ei;

        $ei->project_id              = $project->id;
        $ei->created_by              = $user->id;
        $ei->subject                 = $inputs['subject'];
        $ei->detailed_elaborations   = $inputs['detailed_elaborations'];
        $ei->deadline_to_comply_with = $inputs['deadline_to_comply_with'];
        $ei->status                  = EngineerInstruction::DRAFT;
        $ei->type                    = $user->getAssignedCompany($project)->getContractGroup($project)->group;

        if( $user->isEditor($project) and isset( $inputs['issue_ei'] ) )
        {
            $ei->status     = EngineerInstruction::NOT_YET_CONFIRMED;
            $ei->created_at = Carbon::now();
        }

        $ei = $this->save($ei);

        $this->saveAttachments($ei, $inputs);

        if( $ei->status == EngineerInstruction::NOT_YET_CONFIRMED_TEXT )
        {
            $this->sendEmailNotification($ei->project, $ei, [ Role::INSTRUCTION_ISSUER ], 'engineer_instruction', 'ei.show');
            $this->sendSystemNotification($ei->project, $ei, [ Role::INSTRUCTION_ISSUER ], 'engineer_instruction', 'ei.show');
        }

        return $ei;
    }

    public function update(EngineerInstruction $ei, User $user, array $inputs)
    {
        $ei->created_by              = $user->id;
        $ei->subject                 = $inputs['subject'];
        $ei->detailed_elaborations   = $inputs['detailed_elaborations'];
        $ei->deadline_to_comply_with = $inputs['deadline_to_comply_with'];
        $ei->status                  = EngineerInstruction::DRAFT;
        $ei->type                    = $user->getAssignedCompany($ei->project)->getContractGroup($ei->project)->group;

        if( $user->isEditor($ei->project) and isset( $inputs['issue_ei'] ) )
        {
            $ei->status     = EngineerInstruction::NOT_YET_CONFIRMED;
            $ei->created_at = Carbon::now();
        }

        $ei = $this->save($ei);

        $this->saveAttachments($ei, $inputs);

        if( $ei->status == EngineerInstruction::NOT_YET_CONFIRMED_TEXT )
        {
            $this->sendEmailNotification($ei->project, $ei, [ Role::INSTRUCTION_ISSUER ], 'engineer_instruction', 'ei.show');
            $this->sendSystemNotification($ei->project, $ei, [ Role::INSTRUCTION_ISSUER ], 'engineer_instruction', 'ei.show');
        }

        return $ei;
    }

    public function updateAILink(EngineerInstruction $ei, array $inputs)
    {
        $aiIds = array();

        if( ! empty( $inputs['ais'] ) )
        {
            $aiIds = $inputs['ais'];

            $ei->status = EngineerInstruction::CONFIRMED;
        }

        $ei->architectInstructions()->sync($aiIds);

        $ei = $this->save($ei);

        $this->sendEmailNotification($ei->project, $ei, [ $ei->type, Role::CONTRACTOR ], 'engineer_instruction', 'ei.show');
        $this->sendSystemNotification($ei->project, $ei, [ $ei->type, Role::CONTRACTOR ], 'engineer_instruction', 'ei.show');

        return $ei;
    }

    public function getAffectedArchitectInstructionIds(EngineerInstruction $ei)
    {
        $data = array();

        if( ! $ei->has('architectInstructions') )
        {
            return $data;
        }

        $ais = $ei->architectInstructions;

        foreach($ais as $ai)
        {
            $data[] = $ai->id;
        }

        return $data;
    }

    public function delete(EngineerInstruction $ei)
    {
        // will add checking and see other than DRAFT's status, cannot be deleted.
        if( $ei->status != EngineerInstruction::DRAFT_TEXT )
        {
            throw new \InvalidArgumentException('Only can delete EI that is currently in Draft\'s mode.');
        }

        return $ei->delete();
    }

    private function save(EngineerInstruction $ei)
    {
        $ei->save();

        return $ei;
    }

}