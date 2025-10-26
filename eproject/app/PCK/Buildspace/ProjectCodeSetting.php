<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\Project as ProjectStructure;
use PCK\Subsidiaries\Subsidiary;
use PCK\AccountCodeSettings\SubsidiaryApportionmentRecord;

class ProjectCodeSetting extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_project_code_settings';

    const MAX_PROPORTION  = '100.0';
    const TYPE_PARENT_SUBSIDIARY = 1;
    const TYPE_PHASE_SUBSIDIARY = 2;

    public function subsidiary()
    {
        return $this->belongsTo('PCK\Subsidiaries\Subsidiary', 'eproject_subsidiary_id');
    }

    public static function getSortedSelectedProjectCodeSettingSubsidiary($subsidiaryIds)
    {
        $orderedRecords = Subsidiary::whereIn('id', $subsidiaryIds)->orderBy('parent_id', 'ASC')->get();

        return array_column($orderedRecords->toArray(), 'id');
    }

    public static function getRecordBy(Project $projectStructure, $subsidiaryId)
    {
        return self::where('project_structure_id', $projectStructure->id)
                        ->where('eproject_subsidiary_id', $subsidiaryId)
                        ->first();
    }

    public static function getSelectedSubsidiaries(ProjectStructure $projectStructure)
    {
        return self::where('project_structure_id', $projectStructure->id)
                                ->where('type', self::TYPE_PHASE_SUBSIDIARY)
                                ->get();
    }

    public static function deleteProjectCodeSettings(ProjectStructure $projectStructure, $ids)
    {
        self::where('project_structure_id', $projectStructure->id)->whereNotIn('id', $ids)->delete();
    }

    public static function flushAllRecords(ProjectStructure $projectStructure)
    {
        self::where('project_structure_id', $projectStructure->id)->delete();
    }

    public static function getProportionsGroupedByIds(ProjectStructure $projectStructure, $projectCodeSettings = null)
    {
        $proportionsById = [];
        $project = $projectStructure->mainInformation->getEProjectProject();
        $apportionmentType = $project->accountCodeSetting->apportionmentType;

        if(is_null($projectCodeSettings))
        {
            $projectCodeSettings = self::getSelectedSubsidiaries($projectStructure)->toArray();
            $recordCount = count($projectCodeSettings);
        }
        else
        {
            $recordCount = count($projectCodeSettings);
        }

        $apportionmentTotal = SubsidiaryApportionmentRecord::getApportionmentTotalBySubsidiaries(array_column($projectCodeSettings, 'eproject_subsidiary_id'), $apportionmentType->id);
        $currentProportionTotal = 0.0;
        $count = 1;

        foreach($projectCodeSettings as $projectCodeSetting)
        {
            $subsidiary = Subsidiary::find($projectCodeSetting['eproject_subsidiary_id']);
            $subsidiaryApportionment = SubsidiaryApportionmentRecord::getSubsidiaryApportionment($subsidiary, $apportionmentType->id);

            if(is_null($subsidiaryApportionment)) continue;

            if($count == $recordCount)
            {
                $proportionsById[$projectCodeSetting['id']] = number_format((self::MAX_PROPORTION - $currentProportionTotal), 2, '.', '');
            }
            else
            {
                $proportion = ($subsidiaryApportionment->value / $apportionmentTotal) * 100.0;
                $proportionsById[$projectCodeSetting['id']] = number_format($proportion, 2, '.', '');
                $currentProportionTotal += number_format($proportion, 2, '.', '');
            }

            ++ $count;
        }

        return $proportionsById;
    }
}

