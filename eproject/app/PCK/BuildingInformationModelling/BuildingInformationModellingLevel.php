<?php namespace PCK\BuildingInformationModelling;

use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company;
use PCK\VendorRegistration\CompanyTemporaryDetail;

class BuildingInformationModellingLevel extends Model
{
    protected $table = 'building_information_modelling_levels';

    public static function getBIMLevelSelections()
    {
        $bimLevels = [];

        foreach(self::orderBy('id', 'ASC')->get() as $level)
        {
            $bimLevels[$level->id] = $level->name;
        }

        return $bimLevels;
    }

    public function canBeEdited()
    {
        $isAssignedInCompanyObject = Company::where('bim_level_id', $this->id)->count() > 0;

        if($isAssignedInCompanyObject) return false;

        $isAssignedInCompanyTemporaryDetailObject = CompanyTemporaryDetail::where('bim_level_id', $this->id)->count() > 0;

        if($isAssignedInCompanyTemporaryDetailObject) return false;

        return true;
    }
}