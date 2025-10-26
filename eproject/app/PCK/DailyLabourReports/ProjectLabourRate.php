<?php namespace PCK\DailyLabourReports;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;

class ProjectLabourRate extends Model {

    protected $table = 'project_labour_rates';

    protected $fillable = [ 'project_id', 'labour_type' ];

    CONST LABOUR_TYPE_SKILL      = 1;
    CONST LABOUR_TYPE_SEMI_SKILL = 2;
    CONST LABOUR_TYPE_LABOUR     = 3;

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project', 'project_id');
    }

    public function selectedContractor()
    {
        return $this->belongsTo('PCK\Companies\Company', 'contractor_id');
    }

    public function preDefinedLocationCode()
    {
        return $this->belongsTo('PCK\Buildspace\PreDefinedLocationCode', 'pre_defined_location_code_id');
    }

    public function submittedUser()
    {
        return $this->belongsTo('PCK\Users\User', 'submitted_by');
    }

    public function getTypeName()
    {
        return self::getLabourTypes()[ $this->labour_type ];
    }

    public static function initialise(Project $project)
    {
        $labourTypes = self::getLabourTypes();

        foreach($labourTypes as $key => $value)
        {
            ProjectLabourRate::create(array(
                'project_id'  => $project->id,
                'labour_type' => $key
            ));
        }
    }

    public static function getLabourTypes()
    {
        return array(
            self::LABOUR_TYPE_SKILL      => 'Skill',
            self::LABOUR_TYPE_SEMI_SKILL => 'Semi Skill',
            self::LABOUR_TYPE_LABOUR     => 'Labour'
        );
    }

    public static function saveProjectLabourRateRecords($project, $input, $user)
    {
        $records = self::where("project_id", $project->id)->get();

        $labourTypes = self::getLabourTypes();

        foreach($records as $record)
        {
            foreach($labourTypes as $key => $value)
            {
                if( $record->labour_type == $key )
                {
                    $record->normal_rate_per_hour         = $input[ 'normal_rate_per_hour_' . $key ];
                    $record->ot_rate_per_hour             = $input[ 'ot_rate_per_hour_' . $key ];
                    $record->pre_defined_location_code_id = $input['trade'];
                    $record->contractor_id                = $input['contractor_id']??$input['contractorId'];
                    $record->normal_working_hours         = $input['normal_working_hours'];
                    $record->submitted_by                 = $user->id;
                    $record->save();
                }
            }
        }
    }

    public static function getProjectLabourRateRecords($project, $tradeId)
    {
        $records = self::where("project_id", $project->id)
            ->where("pre_defined_location_code_id", $tradeId)
            ->get();

        if( $records->isEmpty() )
        {
            if( $project->isSubProject() )
            {
                $mainProjectRecords = self::where("project_id", $project->parent_project_id)
                    ->where("pre_defined_location_code_id", $tradeId)
                    ->get();

                $records = $mainProjectRecords;

                if( $mainProjectRecords->isEmpty() )
                {
                    $subProjects = Project::where("parent_project_id", $project->parent_project_id)->get();

                    foreach($subProjects as $subProject)
                    {
                        $subProjectRecords = self::where("project_id", $subProject->id)
                            ->where("pre_defined_location_code_id", $tradeId)
                            ->get();

                        if( ! $subProjectRecords->isEmpty() )
                        {
                            $records = $subProjectRecords;
                        }
                    }
                }
            }

            else
            {
                $subProjects = Project::where("parent_project_id", $project->id)->get();

                foreach($subProjects as $subProject)
                {
                    $subProjectRecords = self::where("project_id", $subProject->id)
                        ->where("pre_defined_location_code_id", $tradeId)
                        ->get();

                    if( ! $subProjectRecords->isEmpty() )
                    {
                        $records = $subProjectRecords;
                    }
                }
            }
        }

        return $records;
    }

    public static function getProjectTrades(Project $project)
    {
        $trades = array();

        $record = self::where("project_id", $project->id)->whereNotNull('pre_defined_location_code_id')->first();

        if( isset( $record ) )
        {
            $trades[ $record->preDefinedLocationCode->id ] = $record->preDefinedLocationCode;
        }

        $subProjects = Project::where("parent_project_id", $project->id)->get();

        foreach($subProjects as $subProject)
        {
            $subProjectRecord = self::where("project_id", $subProject->id)->whereNotNull('pre_defined_location_code_id')->first();

            if( isset( $subProjectRecord ) )
            {
                $trades[ $subProjectRecord->preDefinedLocationCode->id ] = $subProjectRecord->preDefinedLocationCode;
            }
        }

        return $trades;
    }

    public static function getMappedContractorId(Project $project, $tradeId)
    {
        $contractors = array();

        $record = self::where("project_id", $project->id)
            ->where("pre_defined_location_code_id", $tradeId)
            ->first();

        if( isset( $record ) )
        {
            $contractors[ $record->selectedContractor->id ] = $record->selectedContractor;
        }

        $subProjects = Project::where("parent_project_id", $project->id)->get();

        foreach($subProjects as $subProject)
        {
            $subProjectRecord = self::where("project_id", $subProject->id)
                ->where("pre_defined_location_code_id", $tradeId)
                ->first();

            if( isset( $subProjectRecord ) )
            {
                $contractors[ $subProjectRecord->selectedContractor->id ] = $subProjectRecord->selectedContractor;
            }
        }

        return $contractors;
    }

    public static function checkSelectedContractorInTrade($selectedContractorId, $selectedTradeId, $project)
    {
        $record = self::where("project_id", $project->id)
            ->where("pre_defined_location_code_id", $selectedTradeId)
            ->where("contractor_id", $selectedContractorId)
            ->first();

        return $record ? true : false;
    }

}
