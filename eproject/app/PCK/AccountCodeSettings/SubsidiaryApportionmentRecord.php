<?php namespace PCK\AccountCodeSettings;

use Illuminate\Database\Eloquent\Model;
use PCK\Subsidiaries\Subsidiary;
use PCK\Projects\Project;

class SubsidiaryApportionmentRecord extends Model
{
    protected $table = 'subsidiary_apportionment_records';

    public static function getSubsidiaryApportionment(Subsidiary $subsidiary, $apportionmentTypeId)
    {
        return self::where('subsidiary_id', $subsidiary->id)
                    ->where('apportionment_type_id', $apportionmentTypeId)
                    ->first();
    }

    public function isLocked()
    {
        return $this->is_locked;
    }

    public static function getApportionmentTotalBySubsidiaries($subsidiaryIds, $apportionmentTypeId)
    {
        return self::whereIn('subsidiary_id', $subsidiaryIds)->where('apportionment_type_id', $apportionmentTypeId)->sum('value');
    }

    public static function deleteApportionments($subsidiaryIds)
    {
        self::whereNotIn('subsidiary_id', $subsidiaryIds)
            ->where('is_locked', false)
            ->delete();
    }

    public static function lockApportionmentRecords($subsidiaryIds, $inUseApportionmentTypeId)
    {
        foreach($subsidiaryIds as $subsidiaryId)
        {
            $record = self::where('subsidiary_id', $subsidiaryId)->where('apportionment_type_id', $inUseApportionmentTypeId)->where('is_locked', false)->first();

            if(is_null($record)) continue;

            $record->is_locked = true;
            $record->save();
        }
    }

    public static function flushUnusedApportionmentRecords($subsidiaryIds, $inUseApportionmentTypeId)
    {
        self::whereIn('subsidiary_id', $subsidiaryIds)->where('apportionment_type_id', '!=', $inUseApportionmentTypeId)->where('is_locked', false)->delete();
    }
}

