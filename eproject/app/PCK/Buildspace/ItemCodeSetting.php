<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\Project as ProjectStructure;
use PCK\Buildspace\AccountGroup;

class ItemCodeSetting extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_item_code_settings';

    public function accountGroup()
    {
        return $this->belongsTo('PCK\Buildspace\AccountGroup');
    }

    public function accountCode()
    {
        return $this->belongsTo('PCK\Buildspace\AccountCode');
    }

    public static function getItemCodeSettings(ProjectStructure $projectStructure)
    {
        return self::where('project_structure_id', $projectStructure->id)->get();
    }

    public static function getItemCodeSettingsCount(ProjectStructure $projectStructure)
    {
        return self::where('project_structure_id', $projectStructure->id)->count();
    }

    public static function deleteItemCodeSettings(ProjectStructure $projectStructure, AccountGroup $accountGroup, $ids)
    {
        self::where('project_structure_id', $projectStructure->id)
                ->where('account_group_id', $accountGroup->id)
                ->whereNotIn('id', $ids)
                ->delete();
    }

    public static function purgeItemCodeSettings(ProjectStructure $projectStructure)
    {
        self::where('project_structure_id', $projectStructure->id)->delete();
    }
}

