<?php namespace PCK\WeatherRecords;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Projects\Project;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;

class WeatherRecordRepository extends BaseModuleRepository {

    private $weatherRecord;

    protected $events;

    public function __construct(WeatherRecord $weatherRecord, Dispatcher $events)
    {
        $this->weatherRecord = $weatherRecord;
        $this->events        = $events;
    }

    public function find($wrId, $findWithNew = false)
    {
        if( $findWithNew )
        {
            $result = $this->weatherRecord->findOrNew($wrId);
        }
        else
        {
            $result = $this->weatherRecord->with('weatherRecordReports', 'attachments.file')->findOrFail($wrId);
        }

        return $result;
    }

    public function all(Project $project)
    {
        $user = \Confide::user();

        $userCompany      = $user->getAssignedCompany($project);
        $currentUserGroup = ! $userCompany ? false : $userCompany->getContractGroup($project)->group;

        $results = $this->weatherRecord
            ->where('project_id', '=', $project->id)
            ->where('status', '<>', WeatherRecord::PREPARING)
            ->orderBy('id', 'desc')
            ->get();

        foreach($results as $key => $result)
        {
            $recordUserGroup = $result->createdBy->getAssignedCompany($project, $result->created_at)->getContractGroup($project)->group;

            if( $result->status === WeatherRecord::DRAFT_TEXT and $recordUserGroup !== $currentUserGroup )
            {
                $results->forget($key);
            }

            unset( $result );
        }

        return $results;
    }

    public static function getWRCount(Project $project)
    {
        $user = \Confide::user();

        $userCompany      = $user->getAssignedCompany($project);
        $currentUserGroup = ! $userCompany ? false : $userCompany->getContractGroup($project)->group;

        $wrObj = new WeatherRecord();

        $results = $wrObj
            ->where('project_id', '=', $project->id)
            ->where('status', '<>', WeatherRecord::PREPARING)
            ->get();

        foreach($results as $key => $result)
        {
            $recordUserGroup = $result->createdBy->getAssignedCompany($project, $result->created_at)->getContractGroup($project)->group;

            if( $result->status === WeatherRecord::DRAFT_TEXT and $recordUserGroup !== $currentUserGroup )
            {
                $results->forget($key);
            }

            unset( $result );
        }

        return $results->count();
    }

    public function add(Project $project, WeatherRecord $wr, User $user, array $inputs)
    {
        $wr->project_id = $project->id;
        $wr->created_by = $user->id;
        $wr->date       = $inputs['date'];
        $wr->note       = $inputs['note'];
        $wr->status     = WeatherRecord::DRAFT;

        if( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) and $user->isEditor($project) and isset( $inputs['verify'] ) )
        {
            $wr->verified_by = $user->id;
            $wr->status      = WeatherRecord::VERIFIED;
        }
        else
        {
            if( $user->isEditor($project) and isset( $inputs['issue_wr'] ) )
            {
                $wr->created_by = $user->id;
                $wr->status     = WeatherRecord::NOT_YET_VERIFY;
                $wr->created_at = Carbon::now();
            }
        }

        $wr = $this->save($wr);

        $this->saveAttachments($wr, $inputs);

        if( $wr->status == WeatherRecord::NOT_YET_VERIFY_TEXT )
        {
            $this->sendEmailNotification($wr->project, $wr, [ Role::INSTRUCTION_ISSUER ], 'weather_record', 'wr.show');
            $this->sendSystemNotification($wr->project, $wr, [ Role::INSTRUCTION_ISSUER ], 'weather_record', 'wr.show');
        }

        return $wr;
    }

    public function update(WeatherRecord $wr, User $user, array $inputs)
    {
        $wr->date   = $inputs['date'];
        $wr->note   = $inputs['note'];
        $wr->status = WeatherRecord::DRAFT;

        if( $user->hasCompanyProjectRole($wr->project, Role::INSTRUCTION_ISSUER) and $user->isEditor($wr->project) and isset( $inputs['verify'] ) )
        {
            $wr->verified_by = $user->id;
            $wr->status      = WeatherRecord::VERIFIED;
        }
        else
        {
            if( $user->isEditor($wr->project) and isset( $inputs['issue_wr'] ) )
            {
                $wr->created_by = $user->id;
                $wr->status     = WeatherRecord::NOT_YET_VERIFY;
                $wr->created_at = Carbon::now();
            }
        }

        $wr = $this->save($wr);

        $this->saveAttachments($wr, $inputs);

        if( $wr->status == WeatherRecord::NOT_YET_VERIFY_TEXT )
        {
            $this->sendEmailNotification($wr->project, $wr, [ Role::INSTRUCTION_ISSUER ], 'weather_record', 'wr.show');
            $this->sendSystemNotification($wr->project, $wr, [ Role::INSTRUCTION_ISSUER ], 'weather_record', 'wr.show');
        }

        return $wr;
    }

    public function updateArchitectVerificationStatus(WeatherRecord $wr, User $user)
    {
        if( $user->isEditor($wr->project) )
        {
            $wr->verified_by = $user->id;
            $wr->status      = WeatherRecord::VERIFIED;
        }

        $role = $wr->createdBy->getAssignedCompany($wr->project, $wr->created_at)->getContractGroup($wr->project)->group;

        $this->sendEmailNotification($wr->project, $wr, [ $role ], 'weather_record', 'wr.show');
        $this->sendSystemNotification($wr->project, $wr, [ $role ], 'weather_record', 'wr.show');

        return $this->save($wr);
    }

    public function delete(WeatherRecord $wr)
    {
        // will add checking and see other than DRAFT's status, cannot be deleted.
        if( $wr->status != WeatherRecord::DRAFT_TEXT )
        {
            throw new \InvalidArgumentException('Only can delete WR that is currently in Draft\'s mode.');
        }

        return $wr->delete();
    }

    private function save(WeatherRecord $wr)
    {
        $wr->save();

        return $wr;
    }

}